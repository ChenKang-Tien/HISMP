<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id',
        'encode_id',
        'short_name',
        'category_id',
        'na_k_ca',
        'inventory_control',
        'need_packing',
        'scientific_name',
        'product_name',
        'chinese_name',
        'dosage_form_id',
        'unit_id',
        // 'routes_id', 20210219
        'total',
        'note',
        'uses',
        'provider_id',
        'immportant',
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

    public function dosage_form() {
        return $this->belongsTo('App\Models\DosageForm');
    }

    // public function routes() {
    //     return $this->belongsTo('App\Models\RoutesOfAdministration');
    // }

}
