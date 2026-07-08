<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisMachineLong extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_id',
        'dialysis_machine_id',
        'value',
        'doctor_start_id',
        'doctor_end_id',
        'start_date',
        'end_date',
        'nurse_start_id',
        'nurse_end_id',
        'nurse_start_date',
        'nurse_end_date',
        'created_at',
        'updated_at'
    ];
    public function dialysis_machine() {
        return $this->belongsTo('App\Models\DialysisMachine');
    }
    public function doctor_start() {
        return $this->belongsTo('App\Models\User');
    }
    public function doctor_end() {
        return $this->belongsTo('App\Models\User');
    }
    public function nurse_start() {
        return $this->belongsTo('App\Models\User');
    }
    public function nurse_end() {
        return $this->belongsTo('App\Models\User');
    }

    public function dialyzer() {
        return $this->belongsTo('App\Models\MedicalEquipmen', 'value', 'id');
    }

    public function Na_K_Ca(){
        return $this->belongsTo('App\Models\Medicine', 'value', 'id');
    }
}
