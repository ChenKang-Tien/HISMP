<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HospitalAnnouncement extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'date_from',
        'date_to',
        'content',
        'establish_id',
        'note',
        'deleted',
        'created_at',
        'updated_at'
    ];

    public function establish() {
        return $this->belongsTo('App\Models\User');
    }
}

