<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientCard extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'no',
        'card_no',
        'deleted',
        'created_at',
        'updated_at'
    ];
}
