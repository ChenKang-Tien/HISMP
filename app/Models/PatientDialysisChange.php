<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'patient_id',
        'date',
        'status_id',
        'location',
        'note',
        'filled_id',
        'deleted',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    public function filled() {
        return $this->belongsTo('App\Models\User');
    }

    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function status() {
        return $this->belongsTo('App\Models\DialysisModification');
    }
}
