<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientBeforeDialysisMedicine extends Model
{
    protected  = [
        'patient_check_id',
        'medicine_id',
        'medicine',
        'amount',
        'route_id',
        'note',
        'nurse_id',
        'deleted'
    ];
}
