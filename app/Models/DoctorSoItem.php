<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSoItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'time',
        'data',
        'file_string',
        'patient_statement',
        'note',
        'doctor_id',
        'created_at',
        'updated_at'
    ];

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }
    
    public function nurse_record_auxiliary() {
        return $this->belongsTo('App\Models\NurseRecordAuxiliary', 'nurse_record_auxiliary_str', 'name');
    }

    public function doctor() {
        return $this->belongsTo('App\Models\User');
    }
}
