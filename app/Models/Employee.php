<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'daily_salary',
        'kasbon_balance',
        'leave_quota',
        'is_active',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function workLogs()
    {
        return $this->hasMany(WorkLog::class);
    }

    public function kasbonTransactions()
    {
        return $this->hasMany(KasbonTransaction::class);
    }
}
