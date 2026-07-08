<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientMidDialysisRecordNew extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'HCDTTM',
        'BDPS',
        'BDPD',
        'BDPL',
        'BLDF',
        'VEPS',
        'TMP',
        'UF',
        'HPMG',
        'DLTP',
        'PBTP',
        'CDCT',
        'HPST',
        'DLFL',
        'HMMN',
        'HMNO',
        'MAP',
        'KTV',
        'HCT',
        'PKTV',
        'HPDR',
        'HPCV',
        'HPBV',
        'BBADJ',
        'HMOT',
        'PBAF',
        'UFRA',
        'UFTM',
        'UFVL',
        'ADPKID',
        'patient_check_id',
        'created_at',
        'updated_at',
        // 填上所有欄位
    ];

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function patient_card() {
        return $this->belongsTo('App\Models\PatientCard', 'HMNO', 'card_no');
    }
}
