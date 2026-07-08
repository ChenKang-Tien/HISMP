<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAfterBindingRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'item1',
        'nurse1_id',
        'item2',
        'nurse2_id',
        'item3',
        'nurse3_id',
        'item4',
        'nurse4_id',
    ];
    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function nurse1() {
        return $this->belongsTo('App\Models\User');
    }

    public function nurse2() {
        return $this->belongsTo('App\Models\User');
    }

    public function nurse3() {
        return $this->belongsTo('App\Models\User');
    }

    public function nurse4() {
        return $this->belongsTo('App\Models\User');
    }
}
