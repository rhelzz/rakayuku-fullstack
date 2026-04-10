<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function hasRole(UserRole|string ...$roles): bool
    {
        $roleValues = array_map(
            fn ($r) => $r instanceof UserRole ? $r->value : $r,
            $roles
        );

        $userRole = $this->role instanceof UserRole
            ? $this->role->value
            : $this->role;

        return in_array($userRole, $roleValues, strict: true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin);
    }

    public function isFinance(): bool
    {
        return $this->hasRole(UserRole::Finance);
    }

    public function isWarehouse(): bool
    {
        return $this->hasRole(UserRole::Warehouse);
    }

    public function isHr(): bool
    {
        return $this->hasRole(UserRole::Hr);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by');
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'created_by');
    }

    public function kasbonTransactions(): HasMany
    {
        return $this->hasMany(KasbonTransaction::class, 'created_by');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    public function invoicePayments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class, 'created_by');
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'created_by');
    }
}
