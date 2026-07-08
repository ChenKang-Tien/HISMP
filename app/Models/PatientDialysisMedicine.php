<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisMedicine extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_id',
        'medicine_id',
        'medicine',
        'amount',
        'route_id',
        'frequency_id',
        'one_amount',
        'note',
        'doctor_start_id',
        'doctor_end_id',
        'start_date',
        'end_date',
        'nurse_start_id',
        'nurse_start_date',
        'created_at',
        'updated_at'
    ];
    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function medicine() {
        return $this->belongsTo('App\Models\Medicine');
    }

    public function route() {
        return $this->belongsTo('App\Models\RoutesOfAdministration');
    }

    public function frequency() {
        return $this->belongsTo('App\Models\UseFrequency');
    }

    public function doctor_start() {
        return $this->belongsTo('App\Models\User');
    }

    public function doctor_end() {
        return $this->belongsTo('App\Models\User');
    }
}
