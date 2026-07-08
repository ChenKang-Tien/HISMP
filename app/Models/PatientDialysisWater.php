<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisWater extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'amount',
        'patient_ask'
    ];
    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }
}
