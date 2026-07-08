<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAfterAdjustWeight extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'item_id',
        'way_add',
        'weight',
        'nurse_id',
    ];
    public function patient_check() {
        return $this->belongsTo('App\Models\PatientCheck');
    }

    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }

    public function item() {
        return $this->belongsTo('App\Models\WeightAdjustItem');
    }
}
