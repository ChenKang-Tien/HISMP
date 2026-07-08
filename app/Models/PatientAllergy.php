<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAllergy extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
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
        'updated_at'
        // 填上所有欄位
    ];

    public function doctor() {
        return $this->belongsTo('App\Models\User');
    }

    public function delete_doctor() {
        return $this->belongsTo('App\Models\User');
    }

    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }
}
