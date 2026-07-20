<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PatientSupplyCheck extends Model
{
    protected $fillable = [
        'patient_check_id', 'confirmed', 'confirmed_by', 'confirmed_at',
    ];
}
