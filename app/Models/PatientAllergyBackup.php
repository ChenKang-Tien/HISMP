<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAllergyBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_allergy_id',
        'patient_id',
        'edit_date',
        'discover_date',
        'medicine',
        'reaction',
        'note',
        'doctor_id',
        'deleted',
        'delete_doctor_id',
        'delete_date',
        'created_at',
        'updated_at',
        'new_filled_id'
        // 填上所有欄位
    ];
    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }

    public function doctor() {
        return $this->belongsTo('App\Models\User');
    }

    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function patient_allergy() {
        return $this->belongsTo('App\Models\PatientAllergy');
    }
}
