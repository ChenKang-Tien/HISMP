<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAfterPhysiologicalDataBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_after_physiological_data_id',
        'patient_check_id',
        'systolic_blood_pressure_sleep',
        'diastolic_blood_pressure_sleep',
        'systolic_blood_pressure_sit',
        'diastolic_blood_pressure_sit',
        'ak_clear',
        'ak_id',
        'a_clear',
        'a_id',
        'v_clear',
        'v_id',
        'new_filled_id'
    ];
    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function ak() {
        return $this->belongsTo('App\Models\AkValue');
    }

    public function a() {
        return $this->belongsTo('App\Models\ChamberAvValue');
    }

    public function v() {
        return $this->belongsTo('App\Models\ChamberAvValue');
    }

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }
}
