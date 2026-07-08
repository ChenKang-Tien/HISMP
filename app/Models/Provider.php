<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'state_id',
        'date',
        'no',
        'cat_id',
        'uniform_number',
        'principal',
        'com_name_ch',
        'com_name_en',
        'product',
        'web',
        'address',
        'phone',
        'note',
        'bank',
        'bank_branch',
        'bank_account',
        'bank_name',
        'contact_person_1',
        'contact_en_1',
        'contact_nickname_1',
        'contact_phone_1',
        'contact_email_1',
        'contact_note_1',
        'contact_person_2',
        'contact_en_2',
        'contact_nickname_2',
        'contact_phone_2',
        'contact_email_2',
        'contact_note_2',
    ];

    public function state() {
        return $this->belongsTo('App\Models\ProviderState');
    }
    
    public function cat() {
        return $this->belongsTo('App\Models\ProviderCat');
    }
}
