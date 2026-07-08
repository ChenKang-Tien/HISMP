<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisStart extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_id',
        'date',
        'hospital',
        'filled_id',
        'deleted',
        'created_at',
        'updated_at'
    ];
    public function filled() {
        return $this->belongsTo('App\Models\User');
    }
}
