<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageVariant extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'landing_page_id', 'variant_index', 'html_content', 'image_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class);
    }
}
