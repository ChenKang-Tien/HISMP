<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'hospital_id',
        'patient_id',
        'bed_id',
        'triggered_by',
        'triggered_at',
        'handled_by',
        'handled_at',
        'ended_by',
        'ended_at',
        'created_at',
        'updated_at'
    ];

    public function patient()
    {
        return $this->belongsTo('App\Models\Patient');
    }

    public function triggerUser()
    {
        return $this->belongsTo('App\Models\User', 'triggered_by');
    }

    public function endUser()
    {
        return $this->belongsTo('App\Models\User', 'ended_by');
    }
}
