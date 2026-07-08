<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'bed_no',
        'location',
        'type',
        'deleted',
        'created_at',
        'updated_at'
    ];
}
