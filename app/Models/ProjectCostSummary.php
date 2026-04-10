<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCostSummary extends Model
{
    use HasFactory;

    protected $table = 'project_cost_summary';

    protected $fillable = [
        'project_id',
        'hpp_material',
        'hpp_labor',
        'hpp_overhead',
        'hpp_total',
        'selling_price',
        'gross_profit',
        'gross_margin_percent',
        'last_updated_at',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
