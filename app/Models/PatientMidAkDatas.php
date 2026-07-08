<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientMidAkDatas extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'time',
        'ak_id',
        'ns_value',
        'line_fix',
        'pinhole_blood',
        'dispose_id',
        'nurse_id',
        'created_at',
        'updated_at',
        // 填上所有欄位
    ];

    public function ak() {
        return $this->belongsTo('App\Models\AkValue');
    }

    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }

    public function dispose() {
        return $this->belongsTo('App\Models\Dispose');
    }
}
