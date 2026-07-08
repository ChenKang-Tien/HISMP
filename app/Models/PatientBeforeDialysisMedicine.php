<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientBeforeDialysisMedicine extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'medicine_id',
        'medicine',
        'amount',
        'route_id',
        'note',
        'nurse_id',
        'deleted',
        'created_at',
        'updated_at'
    ];
    public function patient() {
        return $this->belongsTo('App\Models\Patient');
    }

    public function medicine() {
        return $this->belongsTo('App\Models\Medicine');
    }

    public function route() {
        return $this->belongsTo('App\Models\RoutesOfAdministration');
    }

    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }
}
