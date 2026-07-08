<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientMidPhysiologicalDataBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_mid_physiological_data_id',
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
        'nurse_id',
        'new_filled_id',
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

    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }
}
