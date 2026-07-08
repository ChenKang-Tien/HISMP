<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientMidBpPDatas extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'time',
        'systolic_blood_pressure',
        'diastolic_blood_pressure',
        'P',
        'dispose_id',
        'machine',
        'nurse_id',
        'display',
        'created_at',
        'updated_at',
        // 填上所有欄位
    ];

    public function dispose() {
        return $this->belongsTo('App\Models\Dispose');
    }

    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }
}
