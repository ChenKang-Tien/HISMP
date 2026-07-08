<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BedPatientCard extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'bed_id',
        'card_id',
        'created_at',
        'updated_at'
    ];
    
    public function bed() {
        return $this->belongsTo('App\Models\Bed');
    }

    public function card() {
        return $this->belongsTo('App\Models\PatientCard');
    }
}
