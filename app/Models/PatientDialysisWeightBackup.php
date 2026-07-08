<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisWeightBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_dialysis_weight_id',
        'patient_id',
        'date',
        'dry_weight',
        'adjust_id',
        'adjust_reason',
        'doctor_id',
        'nurse_id',
        'nurse_date',
        'new_filled_id'
    ];

    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function doctor() {
        return $this->belongsTo('App\Models\User');
    }

    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }
    
    public function adjust() {
        return $this->belongsTo('App\Models\AdjustReason');
    }
}
