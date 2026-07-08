<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorApItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'doctor_so_item_id',
        'time',
        'type',
        'content',
        'data',
        'img_string',
        'doctor_id',
        'nurse_id',
        'nurse_status',
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
}
