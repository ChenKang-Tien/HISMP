<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnterHospitalBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'enter_hospital_id',
        'patient_id',
        'date',
        'hospital',
        'filled_id',
        'deleted',
        'created_at',
        'updated_at',
        'new_filled_id'
        // 填上所有欄位
    ];

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }

    public function filled() {
        return $this->belongsTo('App\Models\User');
    }

    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function enter_hospital() {
        return $this->belongsTo('App\Models\EnterHospital');
    }
}
