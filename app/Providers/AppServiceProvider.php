<?php

namespace App\Providers;

use App\Services\AI\AIProviderInterface;
use App\Services\AI\OpenAIService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AIProviderInterface::class, OpenAIService::class);
    }

    public function boot()
    {
        //
    }
}
