<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class LandingPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'token', 'status',
        'selected_variant_id', 'form_fields', 'settings',
        'input_text', 'input_url',
    ];

    protected $casts = [
        'form_fields' => 'array',
        'settings'    => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($page) {
            $page->token = $page->token ?? Str::random(32);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function variants()
    {
        return $this->hasMany(LandingPageVariant::class);
    }

    public function selectedVariant()
    {
        return $this->belongsTo(LandingPageVariant::class, 'selected_variant_id');
    }

    public function assets()
    {
        return $this->hasMany(LandingPageAsset::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
