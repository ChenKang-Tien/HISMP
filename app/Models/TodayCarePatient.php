<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TodayCarePatient extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'patient_id',
        'nurse_id',
        'date',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }
}
