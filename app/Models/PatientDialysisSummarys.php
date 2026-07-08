<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisSummarys extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_id',
        'patient_check_id',
        'content',
        'link_path',
        'time',
        'add_user_id',
        'created_at',
        'updated_at'
    ];
    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function add_user() {
        return $this->belongsTo('App\Models\User');
    }
}
