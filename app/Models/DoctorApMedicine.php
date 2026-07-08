<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorApMedicine extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'doctor_so_item_id',
        'time',
        'medicine_id',
        'isLong',
        'code',
        'medicine',
        'route_id',
        'frequency_id',
        'amount',
        'total',
        'note',
        'doctor_id',
        'nurse_id',
        'nurse_status',
        'nurse_response_time',
        'doctor_status_id',
        'doctor_status',
        'doctor_status_note',
        'doctor_response_time',
        'deleted',
        'created_at',
        'updated_at'
    ];

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function doctor_so_item() {
        return $this->belongsTo('App\Models\DoctorSoItem');
    }

    public function route() {
        return $this->belongsTo('App\Models\RoutesOfAdministration');
    }

    public function frequency() {
        return $this->belongsTo('App\Models\UseFrequency');
    }
    // public function medicine() {
    //     return $this->belongsTo('App\Models\Medicine');
    // }

    public function doctor() {
        return $this->belongsTo('App\Models\User');
    }

    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }
}
