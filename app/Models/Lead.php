<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'landing_page_id', 'data', 'status', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class)->latest();
    }
}
