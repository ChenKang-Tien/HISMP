<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDialysisMedicineLong extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'patient_dialysis_long_id',
        'type',
        'range_1_1',
        'range_1_2',
        'medicine_1_id',
        'medicine_scientific_name_1',
        'medicine_name_1',
        'medicine_amount_1',
        'medicine_unit_amount_1',
        'medicine_unit_1',
        'medicine_frequency_1_id',
        'route_1_id',
        'range_2_1',
        'range_2_2',
        'medicine_2_id',
        'medicine_scientific_name_2',
        'medicine_name_2',
        'medicine_amount_2',
        'medicine_unit_amount_2',
        'medicine_unit_2',
        'medicine_frequency_2_id',
        'route_2_id',
        'range_3_1',
        'range_3_2',
        'medicine_3_id',
        'medicine_scientific_name_3',
        'medicine_name_3',
        'medicine_amount_3',
        'medicine_unit_amount_3',
        'medicine_unit_3',
        'medicine_frequency_3_id',
        'route_3_id',
        'range_4_1',
        'range_4_2',
        'medicine_4_id',
        'medicine_scientific_name_4',
        'medicine_name_4',
        'medicine_amount_4',
        'medicine_unit_amount_4',
        'medicine_unit_4',
        'medicine_frequency_4_id',
        'route_4_id',
        'created_at',
        'updated_at'
    ];
}
