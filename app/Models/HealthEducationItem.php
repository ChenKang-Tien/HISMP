<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthEducationItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'focus',
        'item',
        'min',
        'max',
        'content',
        'assessments',        // D: 評估選項 (JSON)
        'nursing_activities', // A: 護理活動 (JSON)
        'education_contents', // T: 衛教內容 (JSON)
        'evaluations',        // R: 評值選項 (JSON)
        'updated_at',
        'created_at'
    ];

    protected $casts = [
        'assessments'        => 'array',
        'nursing_activities' => 'array',
        'education_contents' => 'array',
        'evaluations'        => 'array',
    ];
}
