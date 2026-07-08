<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DialysisMachineType extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'machine_no',
        'machine_typ',
        'machine_serial_no',
        'created_at',
        'updated_at'
        // ~~~~~~~~ 填上所有欄位 ~~~~~~~~~
    ];
}
