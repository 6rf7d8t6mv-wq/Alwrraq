<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationalInstitution extends Model
{
    protected $fillable = [
        'official_id',
        'ministerial_number',
        'name_ar',
        'name_en',
        'institution_type',
        'education_stage',
        'ownership_type',
        'gender_type',
        'region',
        'city',
        'district',
        'latitude',
        'longitude',
        'source',
        'source_url',
        'is_active',
        'last_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_active' => 'boolean',
            'last_verified_at' => 'datetime',
        ];
    }
}
