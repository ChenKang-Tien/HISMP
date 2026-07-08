<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientHctInspectionRecordNew extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'patient_id',
        'date',
        'month',
        'week_of_month',
        'hct',
        'hct_add',
        'nurse_id',
        'created_at',
        'updated_at'

        // 填上所有欄位
    ];

    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }
}
