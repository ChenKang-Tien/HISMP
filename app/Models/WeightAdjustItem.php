<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeightAdjustItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'item',
        'default_weight',
        'created_at',
        'updated_at'
    ];
}
