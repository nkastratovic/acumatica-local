<?php

namespace App\Services\Acumatica;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AcumaticaService
{
    private const TOKEN_CACHE_KEY = 'acumatica.access_token';

    private function baseClient(): PendingRequest
{
    return Http::withOptions([
        'verify' => config('acumatica.verify_ssl', true),
    ]);
}

    public function get(
        string $entity,
        array $query = [],
        string $endpoint = 'default'
    ): Response {
        return $this->client()
            ->get($this->entityUrl($entity, $endpoint), $query)
            ->throw();
    }

    public function put(
        string $entity,
        array $data,
        string $endpoint = 'default'
    ): Response {
        return $this->client()
            ->put($this->entityUrl($entity, $endpoint), $data)
            ->throw();
    }

    public function post(
        string $entity,
        array $data,
        string $endpoint = 'default'
    ): Response {
        return $this->client()
            ->post($this->entityUrl($entity, $endpoint), $data)
            ->throw();
    }

    private function client(): PendingRequest
    {
            return $this->baseClient()
        ->acceptJson()
        ->asJson()
        ->withToken($this->accessToken())
        ->connectTimeout(10)
        ->timeout(60);
    }

    private function entityUrl(
        string $entity,
        string $endpoint = 'default'
    ): string {
        $config = config("acumatica.endpoints.{$endpoint}");

        if (! $config) {
            throw new RuntimeException(
                "Acumatica endpoint [{$endpoint}] is not configured."
            );
        }

        return sprintf(
            '%s/entity/%s/%s/%s',
            rtrim(config('acumatica.url'), '/'),
            $config['name'],
            $config['version'],
            ltrim($entity, '/'),
        );
    }

    private function accessToken(): string
    {
        return Cache::remember(
            self::TOKEN_CACHE_KEY,
            now()->addMinutes(50),
            fn () => $this->requestAccessToken()
        );
    }

    private function requestAccessToken(): string
{
    $response = $this->baseClient()
        ->asForm()
        ->post(
            rtrim(config('acumatica.url'), '/').'/identity/connect/token',
            [
                'grant_type' => 'password',
                'client_id' => config('acumatica.auth.client_id'),
                'client_secret' => config('acumatica.auth.client_secret'),
                'username' => config('acumatica.auth.username'),
                'password' => config('acumatica.auth.password'),
                'scope' => config('acumatica.auth.scope'),
            ]
        )
        ->throw();

    $token = $response->json('access_token');

    if (! $token) {
        throw new RuntimeException(
            'Acumatica did not return an access token.'
        );
    }

    return $token;
}
}