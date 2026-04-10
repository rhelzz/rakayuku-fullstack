<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Attendance;
use App\Models\ChartOfAccount;
use App\Models\Employee;
use App\Models\InventoryTransaction;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\KasbonTransaction;
use App\Models\Project;
use App\Models\ProjectBom;
use App\Models\ProjectCostSummary;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use App\Models\WorkLog;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('id_ID');

        // 1. Users
        $roles = ['admin', 'finance', 'warehouse', 'hr'];
        $users = [];
        foreach ($roles as $role) {
            $users[$role] = User::create([
                'name' => ucfirst($role).' User',
                'email' => $role.'@example.com',
                'password' => Hash::make('password'),
                'role' => $role,
            ]);
        }

        // 2. Chart of Accounts
        $coas = [];
        $coaData = [
            ['101', 'Kas Besar', 'asset'],
            ['102', 'Kas Kecil', 'asset'],
            ['103', 'Bank BCA', 'asset'],
            ['402', 'Biaya Bahan', 'expense'],
            ['403', 'Biaya Tukang', 'expense'],
            ['201', 'Pendapatan Proyek', 'revenue'],
        ];
        foreach ($coaData as $c) {
            $coas[] = ChartOfAccount::create([
                'code' => $c[0],
                'name' => $c[1],
                'type' => $c[2],
            ]);
        }

        // 3. Employees
        $employees = [];
        for ($i = 0; $i < 5; $i++) {
            $employees[] = Employee::create([
                'name' => $faker->name,
                'phone' => $faker->phoneNumber,
                'daily_salary' => $faker->randomElement([150000, 200000, 250000]),
                'kasbon_balance' => rand(0, 1000000),
            ]);
        }

        // 4. Projects
        $projects = [];
        for ($i = 1; $i <= 3; $i++) {
            $projects[] = Project::create([
                'code' => 'PRJ-'.str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => 'Pembuatan '.$faker->randomElement(['Lemari', 'Meja', 'Kitchen Set']).' '.$faker->company,
                'customer_name' => $faker->name,
                'customer_phone' => $faker->phoneNumber,
                'customer_address' => $faker->address,
                'selling_price' => $faker->randomElement([15000000, 25000000, 50000000]),
                'status' => $faker->randomElement(['pending', 'in_progress', 'done']),
                'payment_status' => $faker->randomElement(['unpaid', 'partial']),
                'start_date' => now()->subDays(rand(10, 30)),
                'deadline_date' => now()->addDays(rand(10, 30)),
            ]);
        }

        // 5. Items
        $items = [];
        $itemNames = ['Multipleks 18mm', 'HPL Taco', 'Lem Kuning', 'Engsel Sendok', 'Rel Laci'];
        foreach ($itemNames as $idx => $name) {
            $items[] = Item::create([
                'code' => 'ITM-'.str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'name' => $name,
                'unit' => $faker->randomElement(['pcs', 'lembar', 'kg', 'mtr']),
                'current_stock' => rand(10, 100),
                'base_price' => rand(50000, 200000),
                'minimum_stock' => 10,
            ]);
        }

        foreach ($projects as $project) {
            // 6. Project BOM
            $boms = [];
            foreach (array_slice($items, 0, 3) as $item) {
                $qty = rand(2, 10);
                $boms[] = ProjectBom::create([
                    'project_id' => $project->id,
                    'item_id' => $item->id,
                    'estimated_qty' => $qty,
                    'estimated_unit_price' => $item->base_price,
                    'estimated_total' => $qty * $item->base_price,
                ]);
            }

            // 7. Purchase Orders
            $poAmount = array_sum(array_column($boms, 'estimated_total'));
            $po = PurchaseOrder::create([
                'po_number' => 'PO-'.time().'-'.$project->id,
                'supplier_name' => $faker->company,
                'project_id' => $project->id,
                'status' => 'received',
                'total_amount' => $poAmount,
                'po_date' => now()->subDays(rand(5, 15)),
                'created_by' => $users['warehouse']->id,
            ]);

            // Purchase Order Items & Inventory in
            foreach ($boms as $bom) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'item_id' => $bom->item_id,
                    'qty_ordered' => $bom->estimated_qty,
                    'qty_received' => $bom->estimated_qty,
                    'unit_price' => $bom->estimated_unit_price,
                    'total_price' => $bom->estimated_total,
                ]);

                InventoryTransaction::create([
                    'item_id' => $bom->item_id,
                    'project_id' => $project->id,
                    'purchase_order_id' => $po->id,
                    'transaction_type' => 'in',
                    'document_ref' => $po->po_number,
                    'quantity' => $bom->estimated_qty,
                    'balance_after' => $bom->item->current_stock + $bom->estimated_qty,
                    'unit_price' => $bom->estimated_unit_price,
                    'total_price' => $bom->estimated_total,
                    'transaction_date' => now(),
                    'created_by' => $users['warehouse']->id,
                ]);
            }

            // 8. Work Logs & Attendances
            foreach (array_slice($employees, 0, 2) as $employee) {
                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => now()->subDays(1),
                    'status' => 'hadir',
                ]);

                WorkLog::create([
                    'project_id' => $project->id,
                    'employee_id' => $employee->id,
                    'work_date' => now()->subDays(1),
                    'work_type' => 'harian',
                    'rate' => $employee->daily_salary,
                    'amount' => $employee->daily_salary,
                ]);
            }

            // 9. Invoices
            $inv = Invoice::create([
                'invoice_number' => 'INV-'.time().'-'.$project->id,
                'project_id' => $project->id,
                'issue_date' => now(),
                'subtotal' => $project->selling_price,
                'total_amount' => $project->selling_price,
                'remaining_amount' => $project->selling_price,
                'status' => 'sent',
                'invoice_type' => 'progress',
                'created_by' => $users['finance']->id,
            ]);

            InvoiceItem::create([
                'invoice_id' => $inv->id,
                'description' => 'Termin 1 '.$project->name,
                'quantity' => 1,
                'unit_price' => $project->selling_price,
                'total_price' => $project->selling_price,
            ]);

            // 10. Project Cost Summary
            ProjectCostSummary::create([
                'project_id' => $project->id,
                'hpp_material' => $poAmount,
                'hpp_labor' => $employees[0]->daily_salary + $employees[1]->daily_salary,
                'hpp_overhead' => 0,
                'hpp_total' => $poAmount + $employees[0]->daily_salary + $employees[1]->daily_salary,
                'selling_price' => $project->selling_price,
                'gross_profit' => $project->selling_price - ($poAmount + $employees[0]->daily_salary + $employees[1]->daily_salary),
                'last_updated_at' => now(),
            ]);
        }

        // Generate independent transactions
        // Kasbon
        KasbonTransaction::create([
            'employee_id' => $employees[0]->id,
            'type' => 'kredit',
            'amount' => 500000,
            'date' => now(),
            'created_by' => $users['finance']->id,
        ]);

        // Asset
        Asset::create([
            'name' => 'Mesin Potong Kayu',
            'category' => 'Mesin',
            'coa_id' => $coas[0]->id,
            'purchase_date' => now()->subMonths(6),
            'purchase_price' => 5000000,
            'depreciation_rate' => 10,
        ]);

        echo "Database seeded with structured relational data successfully!\n";
    }
}
