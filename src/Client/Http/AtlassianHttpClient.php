<?php

namespace Turbine\Workflow\Client\Http;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use JsonException;
use Turbine\Workflow\Configuration;

class AtlassianHttpClient
{
    private const DEPTH = 512;

    public function __construct(private Configuration $configuration, private Client $client)
    {
    }

    public function get(string $uri): array
    {
        $response = $this->client->get($uri, $this->getDefaultConfig());

        return json_decode($response->getBody()->getContents(), true, self::DEPTH, JSON_THROW_ON_ERROR);
    }

    public function post(string $uri, array $options = []): array
    {
        $options = array_merge([RequestOptions::JSON => $options], $this->getDefaultConfig());
        $response = $this->client->post($uri, $options);
        try {
            return json_decode($response->getBody()->getContents(), true, self::DEPTH, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return [];
        }
    }

    public function put(string $uri, array $options): array
    {
        $options = array_merge([RequestOptions::JSON => $options], $this->getDefaultConfig());
        $response = $this->client->put($uri, $options);
        try {
            return json_decode($response->getBody()->getContents(), true, self::DEPTH, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return [];
        }
    }

    private function getDefaultConfig(): array
    {
        return [
            'auth' => [
                $this->configuration->get(Configuration::JIRA_USERNAME),
                $this->configuration->get(Configuration::JIRA_PASSWORD),
            ],
        ];
    }
}
