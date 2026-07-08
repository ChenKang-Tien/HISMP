<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientBeforePhysiologicalDataBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_before_physiological_data_id',
        'patient_check_id',
        'systolic_blood_pressure',
        'diastolic_blood_pressure',
        'T',
        'P',
        'R',
        'vascular_access',
        'consciousness',
        'skin',
        'new_filled_id',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }
}
