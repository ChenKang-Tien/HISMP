<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DialysisMachineBed extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'bed_no',
        'machine_no_id',
        'created_at',
        'updated_at'
        // ~~~~~~~~ 填上所有欄位 ~~~~~~~~~
    ];
    public function machine_no() {
        return $this->belongsTo('App\Models\DialysisMachineType');
    }
}
