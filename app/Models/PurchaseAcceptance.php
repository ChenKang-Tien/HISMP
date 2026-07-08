<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseAcceptance extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'date',
        'medicine_equipment',
        'medicine_equipment_id',
        'encode_id',
        'provider_id',
        'product_name',
        'unit_id',
        'qualified_quantity',
        'aceptor_id',
        'note',
        'deleted',
        'created_at',
        'updated_at',
    ];
}
