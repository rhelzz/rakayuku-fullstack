<?php

declare(strict_types=1);

namespace App\Actions\Material;

use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\Project;
use App\Models\ProjectBom;
use App\Models\ProjectCostSummary;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProcessMaterialFlowAction
{
    public function handle(User $actor, array $payload): array
    {
        return DB::transaction(function () use ($actor, $payload): array {
            $project = Project::query()->findOrFail((int) $payload['project_id']);

            [$item, $materialRegistered] = $this->resolveItem($payload);

            $requiredQty = (int) $payload['required_qty'];

            $this->upsertMaterialPlan($project, $item, $requiredQty);

            $stockBefore = (int) $item->current_stock;
            $shortage = max(0, $requiredQty - $stockBefore);

            $purchasedQty = 0;
            $purchaseOrder = null;
            $stockInTransaction = null;

            if ($shortage > 0) {
                if (! (bool) ($payload['auto_purchase'] ?? false)) {
                    throw ValidationException::withMessages([
                        'stock' => ["Stok bahan tidak cukup. Kekurangan {$shortage} {$item->unit}."],
                    ]);
                }

                $purchase = $payload['purchase'] ?? [];
                $supplierName = trim((string) ($purchase['supplier_name'] ?? ''));

                if ($supplierName === '') {
                    throw ValidationException::withMessages([
                        'purchase.supplier_name' => ['Supplier wajib diisi saat stok kurang dan auto_purchase aktif.'],
                    ]);
                }

                $purchaseUnitPrice = (float) ($purchase['unit_price'] ?? $item->base_price);

                if ($purchaseUnitPrice <= 0) {
                    throw ValidationException::withMessages([
                        'purchase.unit_price' => ['Harga beli harus lebih dari 0.'],
                    ]);
                }

                $requestedPurchaseQty = (int) ($purchase['qty'] ?? 0);
                $purchasedQty = max($shortage, $requestedPurchaseQty > 0 ? $requestedPurchaseQty : $shortage);

                $purchaseOrder = PurchaseOrder::query()->create([
                    'po_number' => $this->generatePurchaseOrderNumber(),
                    'supplier_name' => $supplierName,
                    'project_id' => $project->id,
                    'status' => 'received',
                    'total_amount' => round($purchasedQty * $purchaseUnitPrice, 2),
                    'notes' => $purchase['notes'] ?? null,
                    'po_date' => now()->toDateString(),
                    'created_by' => $actor->id,
                ]);

                PurchaseOrderItem::query()->create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id' => $item->id,
                    'qty_ordered' => $purchasedQty,
                    'qty_received' => $purchasedQty,
                    'unit_price' => $purchaseUnitPrice,
                    'total_price' => round($purchasedQty * $purchaseUnitPrice, 2),
                ]);

                $stockAfterIn = $stockBefore + $purchasedQty;

                $item->fill([
                    'current_stock' => $stockAfterIn,
                    'base_price' => $this->calculateMovingAveragePrice(
                        $stockBefore,
                        (float) $item->base_price,
                        $purchasedQty,
                        $purchaseUnitPrice,
                    ),
                ])->save();

                $stockInTransaction = InventoryTransaction::query()->create([
                    'item_id' => $item->id,
                    'project_id' => $project->id,
                    'purchase_order_id' => $purchaseOrder->id,
                    'transaction_type' => 'in',
                    'document_ref' => $purchaseOrder->po_number,
                    'quantity' => $purchasedQty,
                    'balance_after' => $stockAfterIn,
                    'unit_price' => $purchaseUnitPrice,
                    'total_price' => round($purchasedQty * $purchaseUnitPrice, 2),
                    'reference_note' => $purchase['notes'] ?? null,
                    'transaction_date' => now()->toDateString(),
                    'created_by' => $actor->id,
                ]);
            }

            $item->refresh();

            if ((int) $item->current_stock < $requiredQty) {
                throw ValidationException::withMessages([
                    'stock' => ['Stok masih tidak mencukupi setelah proses pembelian.'],
                ]);
            }

            $consumptionUnitPrice = (float) $item->base_price;
            $consumptionTotal = round($requiredQty * $consumptionUnitPrice, 2);
            $stockAfterOut = (int) $item->current_stock - $requiredQty;

            $item->fill([
                'current_stock' => $stockAfterOut,
            ])->save();

            $stockOutTransaction = InventoryTransaction::query()->create([
                'item_id' => $item->id,
                'project_id' => $project->id,
                'purchase_order_id' => $purchaseOrder?->id,
                'transaction_type' => 'out',
                'document_ref' => (string) ($payload['document_ref'] ?? 'USAGE-'.Str::upper(Str::random(10))),
                'quantity' => $requiredQty,
                'balance_after' => $stockAfterOut,
                'unit_price' => $consumptionUnitPrice,
                'total_price' => $consumptionTotal,
                'reference_note' => $payload['reference_note'] ?? null,
                'transaction_date' => now()->toDateString(),
                'created_by' => $actor->id,
            ]);

            $materialPlan = $this->applyActualMaterialUsage($project, $item, $requiredQty, $consumptionTotal);
            $costSummary = $this->updateProjectCostSummary($project, $consumptionTotal);

            return [
                'flow' => [
                    'material_registered' => $materialRegistered,
                    'stock_sufficient_initially' => $shortage === 0,
                    'auto_purchase_used' => $shortage > 0,
                ],
                'project' => [
                    'id' => $project->id,
                    'code' => $project->code,
                    'name' => $project->name,
                ],
                'item' => [
                    'id' => $item->id,
                    'code' => $item->code,
                    'name' => $item->name,
                    'unit' => $item->unit,
                    'base_price' => (float) $item->base_price,
                    'current_stock' => (int) $item->current_stock,
                ],
                'stock' => [
                    'required_qty' => $requiredQty,
                    'stock_before' => $stockBefore,
                    'shortage_qty' => $shortage,
                    'purchased_qty' => $purchasedQty,
                    'stock_after' => $stockAfterOut,
                ],
                'purchase' => [
                    'performed' => $purchaseOrder !== null,
                    'purchase_order' => $purchaseOrder?->only(['id', 'po_number', 'supplier_name', 'status', 'total_amount']),
                    'stock_in_transaction_id' => $stockInTransaction?->id,
                ],
                'warehouse_issue' => [
                    'stock_out_transaction_id' => $stockOutTransaction->id,
                    'quantity' => $requiredQty,
                    'unit_price' => $consumptionUnitPrice,
                    'total' => $consumptionTotal,
                ],
                'material_plan' => [
                    'id' => $materialPlan->id,
                    'estimated_qty' => (float) $materialPlan->estimated_qty,
                    'estimated_total' => (float) $materialPlan->estimated_total,
                    'actual_qty' => (float) ($materialPlan->actual_qty ?? 0),
                    'actual_total' => (float) ($materialPlan->actual_total ?? 0),
                ],
                'project_cost_summary' => [
                    'id' => $costSummary->id,
                    'hpp_material' => (float) $costSummary->hpp_material,
                    'hpp_total' => (float) $costSummary->hpp_total,
                    'gross_profit' => (float) $costSummary->gross_profit,
                    'gross_margin_percent' => (float) $costSummary->gross_margin_percent,
                ],
            ];
        });
    }

    private function resolveItem(array $payload): array
    {
        if (isset($payload['item_id'])) {
            $item = Item::query()->lockForUpdate()->findOrFail((int) $payload['item_id']);

            return [$item, true];
        }

        $itemCode = trim((string) ($payload['item_code'] ?? ''));

        if ($itemCode !== '') {
            $item = Item::query()->lockForUpdate()->where('code', $itemCode)->first();

            if ($item instanceof Item) {
                return [$item, true];
            }
        }

        $itemName = trim((string) $payload['item_name']);
        $itemUnit = trim((string) $payload['item_unit']);

        $item = Item::query()
            ->lockForUpdate()
            ->where('name', $itemName)
            ->where('unit', $itemUnit)
            ->first();

        if ($item instanceof Item) {
            return [$item, true];
        }

        $item = Item::query()->create([
            'code' => $itemCode !== '' ? $itemCode : $this->generateItemCode($itemName),
            'name' => $itemName,
            'unit' => $itemUnit,
            'current_stock' => (int) ($payload['initial_stock'] ?? 0),
            'base_price' => (float) $payload['base_price'],
            'minimum_stock' => (int) ($payload['minimum_stock'] ?? 0),
            'is_active' => true,
        ]);

        return [$item, false];
    }

    private function upsertMaterialPlan(Project $project, Item $item, int $requiredQty): void
    {
        $estimatedTotal = round($requiredQty * (float) $item->base_price, 2);

        $materialPlan = ProjectBom::query()
            ->where('project_id', $project->id)
            ->where('item_id', $item->id)
            ->first();

        if ($materialPlan instanceof ProjectBom) {
            $materialPlan->estimated_qty = (float) $materialPlan->estimated_qty + $requiredQty;
            $materialPlan->estimated_unit_price = (float) $item->base_price;
            $materialPlan->estimated_total = (float) $materialPlan->estimated_total + $estimatedTotal;
            $materialPlan->save();

            return;
        }

        ProjectBom::query()->create([
            'project_id' => $project->id,
            'item_id' => $item->id,
            'estimated_qty' => $requiredQty,
            'estimated_unit_price' => (float) $item->base_price,
            'estimated_total' => $estimatedTotal,
            'actual_qty' => 0,
            'actual_total' => 0,
        ]);
    }

    private function applyActualMaterialUsage(
        Project $project,
        Item $item,
        int $requiredQty,
        float $consumptionTotal,
    ): ProjectBom {
        $materialPlan = ProjectBom::query()
            ->where('project_id', $project->id)
            ->where('item_id', $item->id)
            ->firstOrFail();

        $materialPlan->actual_qty = (float) ($materialPlan->actual_qty ?? 0) + $requiredQty;
        $materialPlan->actual_total = (float) ($materialPlan->actual_total ?? 0) + $consumptionTotal;
        $materialPlan->save();

        return $materialPlan;
    }

    private function updateProjectCostSummary(Project $project, float $consumptionTotal): ProjectCostSummary
    {
        $costSummary = ProjectCostSummary::query()->firstOrCreate(
            ['project_id' => $project->id],
            [
                'hpp_material' => 0,
                'hpp_labor' => 0,
                'hpp_overhead' => 0,
                'hpp_total' => 0,
                'selling_price' => (float) $project->selling_price,
                'gross_profit' => 0,
                'gross_margin_percent' => 0,
                'last_updated_at' => now(),
            ],
        );

        $costSummary->hpp_material = round((float) $costSummary->hpp_material + $consumptionTotal, 2);
        $costSummary->hpp_total = round(
            (float) $costSummary->hpp_material
            + (float) $costSummary->hpp_labor
            + (float) $costSummary->hpp_overhead,
            2,
        );

        $sellingPrice = (float) ($costSummary->selling_price ?? $project->selling_price ?? 0);

        $costSummary->selling_price = $sellingPrice;
        $costSummary->gross_profit = round($sellingPrice - (float) $costSummary->hpp_total, 2);
        $costSummary->gross_margin_percent = $sellingPrice > 0
            ? round(((float) $costSummary->gross_profit / $sellingPrice) * 100, 2)
            : 0;
        $costSummary->last_updated_at = now();
        $costSummary->save();

        $project->fill([
            'actual_hpp' => $costSummary->hpp_total,
            'gross_profit' => $costSummary->gross_profit,
        ])->save();

        return $costSummary;
    }

    private function calculateMovingAveragePrice(
        int $stockBefore,
        float $basePriceBefore,
        int $purchaseQty,
        float $purchaseUnitPrice,
    ): float {
        $totalQty = $stockBefore + $purchaseQty;

        if ($totalQty <= 0) {
            return $basePriceBefore;
        }

        $totalCost = ($stockBefore * $basePriceBefore) + ($purchaseQty * $purchaseUnitPrice);

        return round($totalCost / $totalQty, 2);
    }

    private function generateItemCode(string $itemName): string
    {
        $baseCode = Str::upper(Str::limit(preg_replace('/[^A-Za-z0-9]/', '', $itemName) ?: 'ITEM', 6, ''));

        do {
            $code = sprintf('ITM-%s-%s', $baseCode, Str::upper(Str::random(4)));
        } while (Item::query()->where('code', $code)->exists());

        return $code;
    }

    private function generatePurchaseOrderNumber(): string
    {
        do {
            $number = sprintf('PO-%s-%s', now()->format('YmdHis'), Str::upper(Str::random(4)));
        } while (PurchaseOrder::query()->where('po_number', $number)->exists());

        return $number;
    }
}
