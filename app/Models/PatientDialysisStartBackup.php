<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisStartBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_disease_id',
        'patient_id',
        'date',
        'hospital',
        'filled_id',
        'deleted',
        'new_filled_id',
        'created_at',
        'updated_at'
    ];
    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }
}
