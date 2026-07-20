<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientRestraintCheck extends Model
{
    protected $table = 'patient_restraint_checks';

    protected $fillable = [
        'protective_equipment_id',
        'patient_check_id',
        'result',
        'note',
        'nurse_id',
    ];

    public function protectiveEquipment()
    {
        return $this->belongsTo(PatientProtectiveEquipment::class, 'protective_equipment_id');
    }

    public function nurse()
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }
}
