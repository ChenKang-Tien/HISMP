<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisWeight extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_id',
        'date',
        'dry_weight',
        'adjust_id',
        'adjust_reason',
        'doctor_id',
        'nurse_id',
        'nurse_date',
        'status',
        'post_weight' // 新增對應欄位
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

    public function adjust() {
        return $this->belongsTo('App\Models\AdjustReason');
    }
}
