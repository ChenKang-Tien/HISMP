<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DialysisMachine extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'deleted',
        'created_at',
        'updated_at'
    ];
}
