<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DialysisActiveSession extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_id',
        'patient_check_id',
        'current_hmno',
        'last_uf',
        'status',
        'created_at',
        'updated_at'
    ];

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }
}
