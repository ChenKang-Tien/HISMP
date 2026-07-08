<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientVascularAccessRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_id',
        'date',
        'surgery_id',
        'hospital',
        'doctor',
        'reason_id',
        'note',
        'vascular_access_type_id',
        'left_right',
        'location',
        'sign_id',
        'image_path',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    public function sign() {
        return $this->belongsTo('App\Models\User');
    }

    public function vascular_access_type(){
        return $this->belongsTo(VascularAccessType::class, 'vascular_access_type_id');
    }

    public function surgery() {
        return $this->belongsTo('App\Models\VascularAccessSurgeries');
    }

    public function reason() {
        return $this->belongsTo('App\Models\VascularAccessReason');
    }
    
}

