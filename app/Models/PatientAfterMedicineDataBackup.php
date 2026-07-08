<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAfterMedicineDataBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_after_medicine_data_id',
        'patient_check_id',
        'medicine_id',
        'amount',
        'nurse_id',
        'new_filled_id',
        'created_at',
        'updated_at'
    ];
    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }
    public function medicine() {
        return $this->belongsTo('App\Models\Medicine');
    }
}
