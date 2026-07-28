<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiServiceHealthService
{
    public function status(): string
    {
        if ($this->configuredProviderCount() > 0 || $this->localServiceOnline()) {
            return 'online';
        }

        return 'offline';
    }

    public function detail(): string
    {
        $providerCount = $this->configuredProviderCount();
        $totalProviders = count($this->cloudProviders());

        if ($providerCount === $totalProviders && $providerCount > 0) {
            return "{$providerCount} cloud AI providers configured for online recognition.";
        }

        if ($providerCount > 0) {
            return "{$providerCount} of {$totalProviders} cloud AI providers configured for online recognition.";
        }

        if ($this->localServiceOnline()) {
            return 'Local AI model adapter is reachable online.';
        }

        return 'Recognition needs configured AI provider keys or a reachable AI service.';
    }

    public function configuredProviderCount(): int
    {
        return collect($this->cloudProviders())
            ->filter(fn (string $provider) => filled(config("services.ai.providers.{$provider}.key")))
            ->count();
    }

    private function localServiceOnline(): bool
    {
        $url = rtrim((string) config('services.ai.url'), '/');
        if ($url === '') return false;

        try {
            return Http::timeout((int) config('services.ai.health_timeout', 12))
                ->retry(1, 500)
                ->get($url.'/health')
                ->ok();
        } catch (\Throwable) {
            return false;
        }
    }

    private function cloudProviders(): array
    {
        return array_values(array_filter(
            config('services.ai.provider_order', []),
            fn (string $provider) => $provider !== 'local'
        ));
    }
}
