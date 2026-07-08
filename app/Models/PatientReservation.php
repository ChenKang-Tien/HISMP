<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientReservation extends Model
{
    
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_id',
        'date',
        'status',
        'morning_noon_night',
        'machine_bed_id',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'status' => 'integer',
        'morning_noon_night' => 'integer',
    ];

    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function machine_bed() {
        return $this->belongsTo('App\Models\BedPatientCard');
    }
}
