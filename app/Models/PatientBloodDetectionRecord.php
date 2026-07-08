<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientBloodDetectionRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_id',
        'date',
        'software_code',
        'date_1',
        'date_2',
        'date_3',
        'value_1',
        'value_2',
        'value_3',
        'flow_1',
        'flow_2',
        'flow_3',
        'recirculation_1',
        'recirculation_2',
        'recirculation_3',
        'machine_1',
        'machine_2',
        'machine_3',
        'transonic_1',
        'transonic_2',
        'transonic_3',
        'nurse_1_id',
        'nurse_2_id',
        'nurse_3_id',
        'abnormal_alarm_1',
        'abnormal_alarm_2',
        'abnormal_alarm_3',
        'abnormal_date_1',
        'abnormal_date_2',
        'abnormal_date_3',
        'abnormal_note_1',
        'abnormal_note_2',
        'abnormal_note_3',
        'abnormal_nurse_1_id',
        'abnormal_nurse_2_id',
        'abnormal_nurse_3_id',
        'created_at',
        'updated_at'
    ];
    public function patient() {
        return $this->belongsTo('App\Models\User');
    }
    public function nurse_1() {
        return $this->belongsTo('App\Models\User');
    }
    public function nurse_2() {
        return $this->belongsTo('App\Models\User');
    }
    public function nurse_3() {
        return $this->belongsTo('App\Models\User');
    }
    public function abnormal_nurse_1() {
        return $this->belongsTo('App\Models\User');
    }
    public function abnormal_nurse_2() {
        return $this->belongsTo('App\Models\User');
    }
    public function abnormal_nurse_3() {
        return $this->belongsTo('App\Models\User');
    }
}
