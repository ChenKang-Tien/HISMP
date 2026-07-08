<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAfterPhysiologicalDatas extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'systolic_blood_pressure_sleep',
        'diastolic_blood_pressure_sleep',
        'systolic_blood_pressure_sit',
        'diastolic_blood_pressure_sit',
        'ak_clear',
        'ak_id',
        'ak_content',
        'a_clear',
        'a_id',
        'a_content',
        'v_clear',
        'v_id',
        'v_content'
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
}
