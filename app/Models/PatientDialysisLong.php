<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisLong extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_id',
        'start_date',
        'end_date',
        'doctor_id',
        'nurse_start_id',
        'nurse_end_id',
        'nurse_start_date',
        'nurse_end_date'
    ];

    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function doctor() {
        return $this->belongsTo('App\Models\User');
    }

    public function nurse_start() {
        return $this->belongsTo('App\Models\User');
    }
    public function nurse_end() {
        return $this->belongsTo('App\Models\User');
    }
}
