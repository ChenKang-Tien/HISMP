<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientConditionDisposal extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'nurse_record_auxiliary_id',
        'nurse_disposal',
        'doctor_order',
        'created_at',
        'updated_at'
    ];
}
