<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeparinSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'initial',
        'maintain',
        'initial_maintain',
        'updated_at',
        'created_at'
    ];
}
