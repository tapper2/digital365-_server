<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageStyle extends Model
{
    protected $fillable = [
        'name_he', 'name_en', 'tags_he', 'suitable_for_he',
        'image_prompt', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
