<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allergy extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'class',
        'name',
        'deleted',
        'created_at',
        'updated_at'
    ];
}
