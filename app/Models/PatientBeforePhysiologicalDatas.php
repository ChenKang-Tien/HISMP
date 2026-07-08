<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientBeforePhysiologicalDatas extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'type',
        'systolic_blood_pressure',
        'diastolic_blood_pressure',
        'T',
        'P',
        'R',
        'fs',
        'needle_location',
        'needle_number',
        'vascular_access_type',
        'vascular_access_location',
        'vascular_access',
        'consciousness',
        'skin',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

}
