<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientProtectiveEquipment extends Model
{
    protected $table = 'patient_protective_equipments';

    protected $fillable = [
        'patient_check_id',
        'equipment',
        'other_name',
        'nurse_id',
    ];

    protected $casts = [
        'equipment' => 'array',
    ];

    public function nurse()
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function patientCheck()
    {
        return $this->belongsTo(PatientCheck::class);
    }

    public function restraintChecks()
    {
        return $this->hasMany(PatientRestraintCheck::class, 'protective_equipment_id');
    }
}
