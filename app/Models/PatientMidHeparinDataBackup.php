<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientMidHeparinDataBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_mid_heparin_id',
        'patient_check_id',
        'time',
        'heparin_set',
        'heparin_remain',
        'heparin_injection',
        'nurse_id',
        'new_filled_id',
        'created_at',
        'updated_at',
        // 填上所有欄位
    ];
    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }
}
