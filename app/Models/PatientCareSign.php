<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PatientCareSign extends Model
{
    protected $fillable = [
        'patient_check_id', 'time_slot',
        'fir_has', 'fir_minutes', 'fir_reason',
        'ak', 'bleed', 'tube', 'ns_ml',
        'manual_bp_systolic', 'manual_bp_diastolic', 'manual_pr',
        'nurse_id',
    ];
}
