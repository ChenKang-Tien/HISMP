<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisInspectionTestRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_id',
        'items',
        'item_values',
        'date',
        'status',
        'created_at',
        'updated_at'
    ];

    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

}
