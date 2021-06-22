<?php

namespace Turbine\Workflow\Client\Http;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\RequestOptions;
use Turbine\Workflow\Configuration;

class GitlabHttpClient
{

    public function __construct(private Configuration $configuration, private Client $client)
    {
    }

    public function post(string $uri, array $options = []): array
    {
        try {
            $gitlabResponse = $this->client->post(
                $uri,
                [
                    RequestOptions::HEADERS => $this->getHeaders(),
                    RequestOptions::JSON => $options,
                ]
            );
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

    public function get(string $uri): array
    {
        try {
            $gitlabResponse = $this->client->get(
                $uri,
                [
                    RequestOptions::HEADERS => $this->getHeaders(),
                ]
            );
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

    public function delete(string $uri): array
    {
        try {
            $gitlabResponse = $this->client->delete(
                $uri,
                [
                    RequestOptions::HEADERS => $this->getHeaders(),
                ]
            );
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

    private function getHeaders(): array
    {
        return [
            'Private-Token' => $this->configuration->get(Configuration::GITLAB_PERSONAL_ACCESS_TOKEN),
            'Content-Type' => 'application/json',
        ];
    }
}
