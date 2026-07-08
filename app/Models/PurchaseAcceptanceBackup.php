<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseAcceptanceBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'purchase_acceptance_id',
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
    public function aceptor() {
        return $this->belongsTo('App\Models\User');
    }
}
