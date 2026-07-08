<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientReservationConflict extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_id',
        'times',
        'start_date',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }
}
