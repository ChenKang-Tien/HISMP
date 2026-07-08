<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            // 獲取當前最大的流水號（負數）
            $maxSerial = Item::min('serial_number');

            // 如果還沒有記錄，從 -1 開始
            // 否則遞減最大流水號
            $item->serial_number = $maxSerial ? $maxSerial - 1 : -1;
        });
    }
}
