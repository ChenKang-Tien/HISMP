<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'phone',
        'fax',
        'address',
        'web',
        'email',
        'owner',
        'nurse_chief',
        'time_1',
        'time_2',
        'time_3',
        'mon_1',
        'mon_2',
        'mon_3',
        'tue_1',
        'tue_2',
        'tue_3',
        'wed_1',
        'wed_2',
        'wed_3',
        'thu_1',
        'thu_2',
        'thu_3',
        'fri_1',
        'fri_2',
        'fri_3',
        'sat_1',
        'sat_2',
        'sat_3',
        'sun_1',
        'sun_2',
        'sun_3',
        'time_note',
        'dialyzer',
        'bed',
        'weight',
        'sphygmomanometer',
        'hct',
        'updated_at',
        'created_at'
    ];

    protected $casts = [
        'mon_1' => 'boolean','mon_2' => 'boolean','mon_3' => 'boolean',
        'tue_1' => 'boolean','tue_2' => 'boolean','tue_3' => 'boolean',
        'wed_1' => 'boolean','wed_2' => 'boolean','wed_3' => 'boolean',
        'thu_1' => 'boolean','thu_2' => 'boolean','thu_3' => 'boolean',
        'fri_1' => 'boolean','fri_2' => 'boolean','fri_3' => 'boolean',
        'sat_1' => 'boolean','sat_2' => 'boolean','sat_3' => 'boolean',
        'sun_1' => 'boolean','sun_2' => 'boolean','sun_3' => 'boolean',
    ];

    public function nurse_leader()
    {
        return $this->belongsTo(User::class, 'nurse_chief');
    }

    public function boss()
    {
        return $this->belongsTo(User::class, 'owner');
    }
}
