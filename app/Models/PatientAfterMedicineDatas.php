<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAfterMedicineDatas extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'medicine_id',
        'amount',
        'nurse_id',
        'created_at',
        'updated_at'
    ];
    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }

    public function medicine() {
        return $this->belongsTo('App\Models\Medicine');
    }
}
