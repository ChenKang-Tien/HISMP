<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_check_id',
        'patient_id',
        'nurse_id',
        'type',
        'title',
        'description',
        'status',
        'reported_at',
    ];

    public function patientCheck()
    {
        return $this->belongsTo(PatientCheck::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function nurse()
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }
}
