<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientChangeBedRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'from_hmno',
        'to_hmno',
        'at_uf',
        'change_time',
        'created_at',
        'updated_at'
    ];

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }
}
