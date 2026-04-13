<?php

declare(strict_types=1);

namespace Tests\Feature\Material;

use App\Enums\UserRole;
use App\Models\Item;
use App\Models\Project;
use App\Models\ProjectCostSummary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProcessMaterialFlowApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_material_flow_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/material-flow/process', [
            'project_id' => 1,
            'required_qty' => 1,
            'auto_purchase' => false,
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthorized.');
    }

    public function test_process_material_flow_returns_json_validation_error_for_unknown_item_id(): void
    {
        $project = $this->createProject();
        $token = $this->createAuthToken();

        $response = $this->withToken($token)->postJson('/api/v1/material-flow/process', [
            'project_id' => $project->id,
            'item_id' => 10,
            'required_qty' => 5,
            'auto_purchase' => false,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.')
            ->assertJsonPath('errors.item_id.0', 'The selected item id is invalid.');
    }

    public function test_process_material_flow_registers_item_purchases_and_updates_project_cost(): void
    {
        $project = $this->createProject();
        $token = $this->createAuthToken();

        $response = $this->withToken($token)->postJson('/api/v1/material-flow/process', [
            'project_id' => $project->id,
            'required_qty' => 10,
            'item_name' => 'Multipleks 18mm',
            'item_unit' => 'lembar',
            'base_price' => 100000,
            'initial_stock' => 2,
            'minimum_stock' => 1,
            'auto_purchase' => true,
            'purchase' => [
                'supplier_name' => 'PT Kayu Maju',
                'unit_price' => 120000,
                'qty' => 10,
                'notes' => 'Pembelian kekurangan stok',
            ],
            'reference_note' => 'Kebutuhan proyek utama',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.flow.material_registered', false)
            ->assertJsonPath('data.flow.stock_sufficient_initially', false)
            ->assertJsonPath('data.purchase.performed', true)
            ->assertJsonPath('data.stock.required_qty', 10)
            ->assertJsonPath('data.stock.purchased_qty', 10);

        $item = Item::query()->firstOrFail();

        $this->assertSame('Multipleks 18mm', $item->name);
        $this->assertSame(2, (int) $item->current_stock);

        $this->assertDatabaseCount('purchase_orders', 1);
        $this->assertDatabaseCount('inventory_transactions', 2);
        $this->assertDatabaseCount('project_cost_summary', 1);

        $summary = ProjectCostSummary::query()->firstOrFail();
        $this->assertGreaterThan(0, (float) $summary->hpp_material);
    }

    public function test_process_material_flow_uses_existing_stock_without_purchase(): void
    {
        $project = $this->createProject();
        $token = $this->createAuthToken();

        $item = Item::query()->create([
            'code' => 'ITM-STOCK-01',
            'name' => 'HPL Taco',
            'unit' => 'lembar',
            'current_stock' => 20,
            'base_price' => 50000,
            'minimum_stock' => 2,
            'is_active' => true,
        ]);

        $response = $this->withToken($token)->postJson('/api/v1/material-flow/process', [
            'project_id' => $project->id,
            'item_id' => $item->id,
            'required_qty' => 5,
            'auto_purchase' => false,
            'reference_note' => 'Pemakaian rutin',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.flow.material_registered', true)
            ->assertJsonPath('data.flow.stock_sufficient_initially', true)
            ->assertJsonPath('data.purchase.performed', false)
            ->assertJsonPath('data.stock.stock_after', 15);

        $item->refresh();

        $this->assertSame(15, (int) $item->current_stock);
        $this->assertDatabaseCount('purchase_orders', 0);
        $this->assertDatabaseCount('inventory_transactions', 1);

        $this->assertDatabaseHas('inventory_transactions', [
            'item_id' => $item->id,
            'project_id' => $project->id,
            'transaction_type' => 'out',
            'quantity' => 5,
        ]);

        $summary = ProjectCostSummary::query()->firstOrFail();
        $this->assertSame(250000.0, (float) $summary->hpp_material);
    }

    public function test_process_material_flow_returns_error_when_stock_short_and_auto_purchase_disabled(): void
    {
        $project = $this->createProject();
        $token = $this->createAuthToken();

        $item = Item::query()->create([
            'code' => 'ITM-LOW-01',
            'name' => 'Engsel Premium',
            'unit' => 'pcs',
            'current_stock' => 1,
            'base_price' => 15000,
            'minimum_stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->withToken($token)->postJson('/api/v1/material-flow/process', [
            'project_id' => $project->id,
            'item_id' => $item->id,
            'required_qty' => 6,
            'auto_purchase' => false,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Material flow validation failed.');

        $this->assertStringContainsString(
            'Stok bahan tidak cukup',
            (string) $response->json('errors.stock.0'),
        );

        $this->assertDatabaseCount('purchase_orders', 0);
        $this->assertDatabaseCount('inventory_transactions', 0);
        $this->assertDatabaseCount('project_cost_summary', 0);
    }

    private function createAuthToken(): string
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        return $user->createToken('phpunit')->plainTextToken;
    }

    private function createProject(): Project
    {
        return Project::query()->create([
            'code' => 'PRJ-'.Str::upper(Str::random(6)),
            'name' => 'Project Testing Material Flow',
            'customer_name' => 'PT Contoh Klien',
            'customer_phone' => '08123456789',
            'customer_address' => 'Jl. Testing No. 1',
            'selling_price' => 50000000,
            'status' => 'in_progress',
            'payment_status' => 'unpaid',
        ]);
    }
}
