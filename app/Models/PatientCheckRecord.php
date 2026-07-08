<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientCheckRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'id_num',
        'weight',
        'image_path',
        'time',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    
}
