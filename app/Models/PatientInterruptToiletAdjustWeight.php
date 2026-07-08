<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientInterruptToiletAdjustWeight extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'interrput_id',
        'item_id',
        'way_add',
        'weight',
        'is_leave',
        'nurse_id',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }
}
