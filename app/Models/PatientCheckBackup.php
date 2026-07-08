<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientCheckBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'patient_reservation_id',
        'date',
        'status',
        'have_dialysis_data',
        'hours',
        'start_time',
        'end_time',
        'prepare_nurse_id',
        'check_nurse_id',
        'care_nurse_id',
        'care_end_nurse_id',
        'measure_weight_before',
        'measure_weight_after',
        'measure_weight_middle',
        'new_filled_id',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    public function patient_reservation() {
        return $this->belongsTo('App\Models\PatientReservation');
    }

    public function prepare_nurse() {
        return $this->belongsTo('App\Models\User');
    }

    public function check_nurse() {
        return $this->belongsTo('App\Models\User');
    }

    public function care_end_nurse() {
        return $this->belongsTo('App\Models\User');
    }

    public function care_nurse() {
        return $this->belongsTo('App\Models\User');
    }

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }
}
