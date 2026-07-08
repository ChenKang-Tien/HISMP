<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientEpoIronMedicine extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_id',
        'epo_iron',
        'range',
        'range_1',
        'range_2',
        'medicine_id',
        'medicine',
        'route_id',
        'frequency_id',
        'one_amount',
        'doctor_start_id',
        'doctor_end_id',
        'start_date',
        'end_date',
        'created_at',
        'updated_at'
    ];
    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function route() {
        return $this->belongsTo('App\Models\RoutesOfAdministration');
    }

    public function frequency() {
        return $this->belongsTo('App\Models\UseFrequency');
    }

    public function rf_medicine() {
        return $this->belongsTo('App\Models\Medicine', 'medicine_id', 'id');
    }
}
