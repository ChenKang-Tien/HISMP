<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalEquipmen extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'encode_id',
        'short_name',
        'category_id',
        'inventory_control',
        'product_name',
        'chinese_name',
        'unit_id',
        'note',
        'uses',
        'provider_id',
        'deleted',
        'created_at',
        'updated_at'
    ];
    public function category() {
        return $this->belongsTo('App\Models\Category');
    }

    public function unit() {
        return $this->belongsTo('App\Models\Unit');
    }

    public function provider() {
        return $this->belongsTo('App\Models\Provider');
    }
}
