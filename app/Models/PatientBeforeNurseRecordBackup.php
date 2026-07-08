<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientBeforeNurseRecordBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_before_nurse_record_id',
        'patient_check_id',
        'time',
        'value',
        'patient_statement',
        'nurse_record_auxiliary_str',
        'nurse_record_auxiliary_value',
        'patient_condition_disposal_str',
        'patient_condition_disposal_value',
        'continue',
        'continue_value',
        'nurse_id',
        'new_filled_id',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];
    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }

    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }

    public function nurse_record_auxiliary() {
        return $this->belongsTo('App\Models\NurseRecordAuxiliary', 'nurse_record_auxiliary_str', 'name');
    }

    public function patient_condition_disposal() {
        return $this->belongsTo('App\Models\PatientConditionDisposal', 'patient_condition_disposal_str', 'nurse_disposal');
    }
}
