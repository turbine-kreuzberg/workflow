<?php

namespace Workflow\Client\Http;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use JsonException;

class AtlassianHttpClient
{
    public const USERNAME = 'JIRA_USERNAME';
    public const PASSWORD = 'JIRA_PASSWORD';

    private Client $client;

    public function __construct()
    {
        $this->client = new Client(
            [
            'auth' => [
                $this->getUsername(),
                $this->getPassword(),
            ],
            ]
        );
    }

    public function get(string $uri): array
    {
        $response = $this->client->get($uri);

        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function post(string $uri, array $options = []): array
    {
        $response = $this->client->post($uri, [RequestOptions::JSON => $options]);
        try {
            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return [];
        }
    }

    public function put(string $uri, array $options): array
    {
        $response = $this->client->put($uri, [RequestOptions::JSON => $options]);
        try {
            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return [];
        }
    }

    private function getUsername(): string
    {
        $envVarname = self::USERNAME;
        if (getenv($envVarname)) {
            return (string)getenv($envVarname);
        }

        throw new Exception('No username provided. Please add to your ".env" file.');
    }

    private function getPassword(): string
    {
        $envVarname = self::PASSWORD;
        if (getenv($envVarname)) {
            return (string)getenv($envVarname);
        }

        throw new Exception('No password provided. Please add to your ".env" file.');
    }
}
