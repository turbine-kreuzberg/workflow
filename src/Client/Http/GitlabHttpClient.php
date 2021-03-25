<?php

namespace Turbine\Workflow\Client\Http;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\RequestOptions;

class GitlabHttpClient
{

    public function __construct(private Client $client)
    {
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

        return json_decode($gitlabResponse->getBody()->getContents(), true);
    }

    public function get(string $uri, array $options = []): array
    {
        try {
            $gitlabResponse = $this->client->get($uri, $options);
        } catch (BadResponseException $exception) {
            if ($exception->getResponse()->getStatusCode() === 401) {
                throw new Exception(
                    'Gitlab answered with 401 Unauthorized: Please check your personal access token in your .env file.'
                );
            }

            throw $exception;
        }

        return json_decode($gitlabResponse->getBody()->getContents(), true);
    }
}
