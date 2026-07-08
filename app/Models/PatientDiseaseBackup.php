<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDiseaseBackup extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'patient_disease_id',
        'patient_id',
        'date',
        'disease',
        'filled_id',
        'deleted',
        'new_filled_id',
        'created_at',
        'updated_at',
    ];

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }
}
