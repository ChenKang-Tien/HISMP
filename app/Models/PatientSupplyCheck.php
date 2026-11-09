<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientSupplyCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'patient_check_id',
        'confirmed',
        'confirmed_by',
        'confirmed_at',
        'created_at',
        'updated_at'
    ];

    public function patient_check()
    {
        return $this->belongsTo(PatientCheck::class, 'patient_check_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
