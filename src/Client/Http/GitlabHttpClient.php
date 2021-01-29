<?php

namespace Workflow\Client\Http;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\RequestOptions;
use Workflow\Configuration;

class GitlabHttpClient
{

    private Client $client;

    public function __construct(private Configuration $configuration)
    {
        $this->client = new Client(
            [
            'headers' => [
                'Private-Token' => $this->configuration->getConfiguration(Configuration::PERSONAL_ACCESS_TOKEN),
                'Content-Type' => 'application/json',
            ],
            ]
        );
    }

    public function post(string $uri, array $options = []): array
    {
        try {
            $gitlabResponse = $this->client->post($uri, [RequestOptions::JSON => $options]);
        } catch (BadResponseException $exception) {
            if ($exception->getResponse()->getStatusCode() === 401) {
                throw new Exception(
                    'Gitlab answered with 401 Unauthorized: Please check your personal access token in your .env file.'
                );
            }

            throw $exception;
        }

        return json_decode((string)$gitlabResponse->getBody(), true);
    }

    public function get(string $uri, array $options = []): array
    {
        try {
            $gitlabResponse = $this->client->get($uri, $options);
        } catch (BadResponseException $exception) {
            if ($exception->getResponse()->getStatusCode() === 401) {
                throw new Exception(
                    'Gitlab answered with 401 Unauthorized: Please check your personal acccess token in your .env file.'
                );
            }

            throw $exception;
        }

        return json_decode($gitlabResponse->getBody()->getContents(), true);
    }

    public function delete(string $uri): void
    {
        $this->client->delete($uri);
    }

    public function put(string $uri): void
    {
        $this->client->put($uri);
    }
}
