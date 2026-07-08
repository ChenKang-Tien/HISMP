<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientNormalMedicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'history_id',
        'create_date',
        'medicine_id',
        'medicine',
        'amount',
        'provider',
        'route_id',
        'frequency_id',
        'one_amount',
        'days',
        'total',
        'note',
        'ration_date',
        'ration_who',
        'ration_by_user',
        'give_date',
        'give_who_id',
        'created_at',
        'updated_at'
        // 填上所有欄位
    ];

    public function history() {
        return $this->belongsTo('App\Models\PatienNormalMedicineHistory');
    }

    public function medicine() {
        return $this->belongsTo('App\Models\Medicine');
    }

    public function route() {
        return $this->belongsTo('App\Models\RoutesOfAdministration');
    }

    public function frequency() {
        return $this->belongsTo('App\Models\UseFrequency');
    }

    public function give_who() {
        return $this->belongsTo('App\Models\User');
    }
}
