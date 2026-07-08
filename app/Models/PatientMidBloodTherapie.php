<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientMidBloodTherapie extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'type',
        'blood_id',
        'rh_id',
        'image_path',
        'amount',
        'start_time',
        'end_time',
        'created_at',
        'updated_at',
        // 填上所有欄位
    ];
    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function blood() {
        return $this->belongsTo('App\Models\Blood');
    }

    public function rh() {
        return $this->belongsTo('App\Models\BloodRh');
    }

}
