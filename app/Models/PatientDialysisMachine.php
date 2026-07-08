<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisMachine extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_reservation_id',
        'machine_serial_no',
        'created_at',
        'updated_at'
    ];
    

    public function patient_reservation() {
        return $this->belongsTo('App\Models\PatientReservation');
    }
}
