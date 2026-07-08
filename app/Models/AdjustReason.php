<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustReason extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'need_note',
        'deleted',
        'created_at',
        'updated_at'
    ];
}
