<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisMachineShortBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_dialysis_machine_short_id',
        'patient_id',
        'dialysis_machine_id',
        'value',
        'test_times_flag',
        'test_times',
        'test_start_date',
        'test_end_date',
        'follow_up_id',
        'doctor_start_id',
        'doctor_end_id',
        'start_date',
        'end_date',
        'nurse_start_id',
        'nurse_end_id',
        'nurse_start_date',
        'nurse_end_date',
        'actual_times',
        'new_filled_id',
        'created_at',
        'updated_at'
    ];
    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function patient_dialysis_machine_long() {
        return $this->belongsTo('App\Models\PatientDialysisMachineLong');
    }

    public function patient_dialysis_long() {
        return $this->belongsTo('App\Models\PatientDialysisLong');
    }
    
    public function dialysis_machine() {
        return $this->belongsTo('App\Models\DialysisMachine');
    }
    public function doctor_start() {
        return $this->belongsTo('App\Models\User');
    }
    public function doctor_end() {
        return $this->belongsTo('App\Models\User');
    }

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }
}
