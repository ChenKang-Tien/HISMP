<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSoItemBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'doctor_so_item_id',
        'patient_check_id',
        'time',
        'patient_statement',
        'nurse_record_auxiliary_id',
        'nurse_record_auxiliary_str',
        'nurse_record_auxiliary_value',
        'doctor_id',
        'new_filled_id',
        'created_at',
        'updated_at'
    ];

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }
    
    public function nurse_record_auxiliary() {
        return $this->belongsTo('App\Models\NurseRecordAuxiliary', 'nurse_record_auxiliary_str', 'name');
    }
}
