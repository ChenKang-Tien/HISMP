<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientInterruptToiletWeightBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_interrupt_toilet_weight_id',
        'patient_check_id',
        'measure_weight_leave',
        'measure_weight_back',
        'measure_weight_back_nurse_id',
        'start_time',
        'end_time',
        'new_filled_id',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function measure_weight_back_nurse()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }
}
