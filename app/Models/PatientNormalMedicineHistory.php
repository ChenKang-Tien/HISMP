<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientNormalMedicineHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'patient_id',
        'date',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    public function patinet() {
        return $this->belongsTo('App\Models\Patient');
    }
}
