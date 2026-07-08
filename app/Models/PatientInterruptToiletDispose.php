<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientInterruptToiletDispose extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_mid_bp_p_data_id',
        'patient_check_id',
        'notify_blood_doctor_id',
        'notify_blood_time',
        'notify_blood_nurse_id',
        'sign_doc_path',
        'sign_doc_time',
        'sign_doc_nurse_id',
        'uf',
        'uf_nurse_id',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    public function patient_mid_bp_p_data() {
        return $this->belongsTo('App\Models\PatientMidBpPDatas');
    }

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }
    public function notify_blood_doctor() {
        return $this->belongsTo('App\Models\User');
    }

    public function notify_blood_nurse() {
        return $this->belongsTo('App\Models\User');
    }

    public function sign_doc_nurse() {
        return $this->belongsTo('App\Models\User');
    }

    public function uf_nurse()
    {
        return $this->belongsTo('App\Models\User');
    }
}
