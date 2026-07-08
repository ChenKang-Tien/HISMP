<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientHctInspectionRecordBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_hct_inspection_record_id',
        'patient_id',
        'date',
        'day_1',
        'day_2',
        'day_3',
        'day_4',
        'day_5',
        'hct_1',
        'hct_2',
        'hct_3',
        'hct_4',
        'hct_5',
        'nurse_1_id',
        'nurse_2_id',
        'nurse_3_id',
        'nurse_4_id',
        'nurse_5_id',
        'new_filled_id',
        'created_at',
        'updated_at'

        // 填上所有欄位
    ];

    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function nurse_1() {
        return $this->belongsTo('App\Models\User');
    }

    public function nurse_2() {
        return $this->belongsTo('App\Models\User');
    }

    public function nurse_3() {
        return $this->belongsTo('App\Models\User');
    }

    public function nurse_4() {
        return $this->belongsTo('App\Models\User');
    }

    public function nurse_5() {
        return $this->belongsTo('App\Models\User');
    }

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }
}
