<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'frequency',
        'range_lower',
        'range_upper',
        'unit',
        'education_summary',
    ];
}
