<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientMidBpPDataBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_mid_bp_p_data_id',
        'patient_check_id',
        'time',
        'systolic_blood_pressure',
        'diastolic_blood_pressure',
        'P',
        'dispose_id',
        'machine',
        'nurse_id',
        'new_filled_id',
        'created_at',
        'updated_at',
        // 填上所有欄位
    ];
    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }
}
