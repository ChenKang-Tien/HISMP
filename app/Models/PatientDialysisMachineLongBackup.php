<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisMachineLongBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_dialysis_machine_long_id',
        'patient_dialysis_long_id',
        'dialysis_machine_id',
        'value',
        'new_filled_id',
        'created_at',
        'updated_at'
    ];
    public function patient_dialysis_long() {
        return $this->belongsTo('App\Models\PatientDialysisLong');
    }
    
    public function dialysis_machine() {
        return $this->belongsTo('App\Models\DialysisMachine');
    }
}
