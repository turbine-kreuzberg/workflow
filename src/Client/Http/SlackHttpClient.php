<?php

declare(strict_types=1);

namespace Turbine\Workflow\Client\Http;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\RequestOptions;

class SlackHttpClient
{
    public function __construct(private Client $client)
    {
    }

    public function post(string $uri, array $options = []): void
    {
        try {
            $this->client->post($uri, [RequestOptions::JSON => $options]);
        } catch (BadResponseException $exception) {
            if ($exception->getResponse()->getStatusCode() === 403) {
                throw new Exception(
                    'Slack answered with 403 Forbidden: Please check your webhook url in your .env file.'
                );
            }

            throw $exception;
        }
    }
}
