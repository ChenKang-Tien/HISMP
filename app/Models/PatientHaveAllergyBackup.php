<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientHaveAllergyBackup extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'patient_have_allergy_id',
        'patient_id',
        'date',
        'have',
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
