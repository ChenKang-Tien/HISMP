<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'nurse_id',
        'filled_date',
        'name',
        'medical_record_no',
        'id_num',
        'birth_date',
        'blood_id',
        'blood_rh_id',
        'gender_id',
        'residence_addr',
        'mailing_addr',
        'mobile_phone',
        'home_phone',
        'company_phone',
        'related_name_1',
        'related_1',
        'related_mobile_phone_1',
        'related_home_phone_1',
        'related_company_phone_1',
        'related_name_2',
        'related_2',
        'related_mobile_phone_2',
        'related_home_phone_2',
        'related_company_phone_2',
        'image_path',
        'table_name',
        'deleted'
    ];

    public function nurse() {
        return $this->belongsTo('App\Models\User');
    }

    public function gender() {
        return $this->belongsTo('App\Models\Gender');
    }

    public function blood() {
        return $this->belongsTo('App\Models\Blood');
    }

    public function blood_rh() {
        return $this->belongsTo('App\Models\BloodRh');
    }
}
