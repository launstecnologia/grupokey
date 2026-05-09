<?php

namespace App\Services;

use App\Core\AutoConfig;

class RealtimeService
{
    private $appId;
    private $key;
    private $secret;
    private $cluster;
    private $enabled;

    public function __construct()
    {
        $this->appId = (string) AutoConfig::get('PUSHER_APP_ID', '');
        $this->key = (string) AutoConfig::get('PUSHER_KEY', '');
        $this->secret = (string) AutoConfig::get('PUSHER_SECRET', '');
        $this->cluster = (string) AutoConfig::get('PUSHER_CLUSTER', 'us2');
        $this->enabled = $this->appId !== '' && $this->key !== '' && $this->secret !== '';
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getPublicConfig(): array
    {
        return [
            'enabled' => $this->enabled,
            'key' => $this->key,
            'cluster' => $this->cluster,
        ];
    }

    public function authorizePrivateChannel(string $socketId, string $channelName): ?array
    {
        if (!$this->enabled || $socketId === '' || $channelName === '') {
            return null;
        }

        $stringToSign = $socketId . ':' . $channelName;
        $signature = hash_hmac('sha256', $stringToSign, $this->secret);

        return [
            'auth' => $this->key . ':' . $signature,
        ];
    }

    public function trigger(string $channel, string $event, array $data): bool
    {
        if (!$this->enabled || $channel === '' || $event === '') {
            return false;
        }

        $body = json_encode([
            'name' => $event,
            'channels' => [$channel],
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
        ], JSON_UNESCAPED_UNICODE);

        $path = '/apps/' . rawurlencode($this->appId) . '/events';
        $params = [
            'auth_key' => $this->key,
            'auth_timestamp' => time(),
            'auth_version' => '1.0',
            'body_md5' => md5($body),
        ];
        ksort($params);
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        $stringToSign = "POST\n{$path}\n{$query}";
        $signature = hash_hmac('sha256', $stringToSign, $this->secret);
        $url = 'https://api-' . $this->cluster . '.pusher.com' . $path . '?' . $query . '&auth_signature=' . $signature;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => 10,
        ]);

        curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }
}

