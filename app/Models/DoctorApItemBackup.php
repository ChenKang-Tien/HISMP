<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorApItemBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'doctor_ap_item_id',
        'patient_check_id',
        'doctor_so_item_id',
        'time',
        'type',
        'content',
        'doctor_id',
        'new_filled_id',
        'deleted',
        'created_at',
        'updated_at'
    ];

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }

    public function doctor_so_item() {
        return $this->belongsTo('App\Models\DoctorSoItem');
    }
}
