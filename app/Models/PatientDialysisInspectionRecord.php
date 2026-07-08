<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisInspectionRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'patient_id',
        'type',
        'date',
        'bun_before',
        'bun_after',
        'creatinine',
        'uric_acid',
        'na',
        'k',
        'ca',
        'p',
        'albumin',
        'globulin',
        'sgot',
        'sgpt',
        'sugar_ac',
        'sugar_pc',
        'hba1c',
        'bilirubin_t',
        'alk_p',
        'tg',
        'cholesterol',
        'ferritin',
        'serum_iron',
        'tibc',
        'TS',
        'anti_hcv',
        'hbsag',
        'anti_hbs',
        'anti_hiv',
        'vdrl',
        'i_pth',
        'aluminum',
        'mg',
        'hb',
        'hct',
        'mcv',
        'platelet',
        'rbc',
        'wbc',
        'c_t_ratio',
        'urr',
        'pcrn',
        'kt_v_gotch',
        'daugirdas',
        'ak_id',
        'dialysis_time',
        'blood_speed',
        'weight_before',
        'weight_after',
        'uf',
        'dry_weight',
        'sign_id',
        'status',
        'deleted',
        'created_at',
        'updated_at'
    ];

    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function ak() {
        return $this->belongsTo('App\Models\ArtificialKidney');
    }

    public function sign() {
        return $this->belongsTo('App\Models\User');
    }
}
