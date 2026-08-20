<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VitalSign extends Model
{
    protected $fillable = [
        'patient_check_id',
        'systolic_pressure',
        'diastolic_pressure',
        'pulse',
        'respiratory_rate',
        'temperature',
        'blood_sugar'
    ];
}
