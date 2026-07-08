<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisChangeBackup extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_dialysis_change_id',
        'patient_id',
        'date',
        'status_id',
        'location',
        'note',
        'filled_id',
        'deleted',
        'created_at',
        'updated_at',
        'new_filled_id'
        // 填上所有欄位
    ];

    public function new_filled() {
        return $this->belongsTo('App\Models\User');
    }

    public function filled() {
        return $this->belongsTo('App\Models\User');
    }

    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function status() {
        return $this->belongsTo('App\Models\DialysisModification');
    }

    public function patient_dialysis_change() {
        return $this->belongsTo('App\Models\PatientDialysisChange');
    }
}
