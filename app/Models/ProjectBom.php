<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectBom extends Model
{
    use HasFactory;

    protected $table = 'project_bom';

    protected $fillable = [
        'project_id',
        'item_id',
        'estimated_qty',
        'estimated_unit_price',
        'estimated_total',
        'actual_qty',
        'actual_total',
        'notes',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
