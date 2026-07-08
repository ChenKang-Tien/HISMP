<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientNotice extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'note_1',
        'note_2',
        'note_3',
        'note_4',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }
}
