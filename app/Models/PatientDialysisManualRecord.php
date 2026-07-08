<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisManualRecord extends Model
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
        'dispose_id',
        'note',
        'ak_id',
        'ns_value',
        'line_fix',
        'pinhole_blood',
        'patient_check_id',
        'deleted',
        'created_at',
        'updated_at',
    ];

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function patient_card() {
        return $this->belongsTo('App\Models\PatientCard', 'HMNO', 'card_no');
    }

    public function dispose() {
        return $this->belongsTo('App\Models\DialysisDispose', 'dispose_id', 'id');
    }

    public function ak() {
        return $this->belongsTo('App\Models\AkValue');
    }
    
}
