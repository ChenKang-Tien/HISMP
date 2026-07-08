<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientMidPhysiologicalDatas extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'time',
        'systolic_blood_pressure',
        'diastolic_blood_pressure',
        'P',
        'heparin_set',
        'heparin_remain',
        'ak_id',
        'line_fix',
        'pinhole_blood',
        'dispose_id',
        'machine',
        'nurse_id',
        'created_at',
        'updated_at',
        // 填上所有欄位
    ];
    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function ak() {
        return $this->belongsTo('App\Models\AkValue');
    }
    
}
