<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LandingPageAsset extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'landing_page_id', 'type', 'path', 'original_name',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return url(Storage::url($this->path));
    }

    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class);
    }
}
