<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NurseWatching extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_check_id',
        'time',
        'content',
        'image_paths',
        'status',
        'nurse_id',
        'created_at',
        'updated_at'
    ];
}
