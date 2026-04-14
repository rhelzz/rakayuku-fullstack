<?php

use App\Models\Item;
use App\Models\Project;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

Route::redirect('/', '/login')->name('home');

Route::inertia('/welcome', 'Welcome')->name('welcome');
Route::inertia('/login', 'Auth/Login')->name('login');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::inertia('/', 'Admin/Dashboard')->name('dashboard');

    Route::get('/material-flow', function () {
        $projects = [];
        $items = [];

        if (Schema::hasTable('projects')) {
            $projects = Project::query()
                ->select(['id', 'code', 'name', 'customer_name', 'status'])
                ->orderByDesc('id')
                ->limit(100)
                ->get()
                ->toArray();
        }

        if (Schema::hasTable('items')) {
            $items = Item::query()
                ->select(['id', 'code', 'name', 'unit', 'current_stock', 'base_price'])
                ->orderByDesc('id')
                ->limit(200)
                ->get()
                ->toArray();
        }

        return Inertia::render('Admin/MaterialFlow', [
            'projects' => $projects,
            'items' => $items,
        ]);
    })->name('material-flow');

    Route::get('/projects', fn () => Inertia::render('Admin/DomainPreview', [
        'moduleKey' => 'projects',
    ]))->name('projects');

    Route::get('/inventory', fn () => Inertia::render('Admin/DomainPreview', [
        'moduleKey' => 'inventory',
    ]))->name('inventory');

    Route::get('/procurement', fn () => Inertia::render('Admin/DomainPreview', [
        'moduleKey' => 'procurement',
    ]))->name('procurement');

    Route::get('/sales', fn () => Inertia::render('Admin/DomainPreview', [
        'moduleKey' => 'sales',
    ]))->name('sales');

    Route::get('/finance', fn () => Inertia::render('Admin/DomainPreview', [
        'moduleKey' => 'finance',
    ]))->name('finance');

    Route::get('/hr', fn () => Inertia::render('Admin/DomainPreview', [
        'moduleKey' => 'hr',
    ]))->name('hr');

    Route::get('/debts-receivables', fn () => Inertia::render('Admin/DomainPreview', [
        'moduleKey' => 'debts',
    ]))->name('debts-receivables');
});
