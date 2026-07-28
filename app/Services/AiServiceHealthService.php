<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiServiceHealthService
{
    public function status(): string
    {
        $url = rtrim((string) config('services.ai.url'), '/');
        if ($url === '') return 'not configured';
        try { return Http::timeout(2)->get($url.'/health')->ok() ? 'online' : 'offline'; }
        catch (\Throwable) { return 'offline'; }
    }
}
