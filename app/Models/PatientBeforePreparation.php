<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientBeforePreparation extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'name',
        'medicine_equipment',
        'medicine_equipment_id',
        'product_name',
        'use',
        'number',
        'amount',
        'unit',
        'location_id',
        'nurse_id',
        'check_time',
        'check_nurse_id',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function medicine(){
        return $this->hasOne('App\Models\Medicine', 'id', 'medicine_equipment_id');
    }

    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }

    public function check_nurse() {
        return $this->belongsTo('App\Models\User');
    }
    

}
