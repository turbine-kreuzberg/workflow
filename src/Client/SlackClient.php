<?php

namespace Turbine\Workflow\Client;

use GuzzleHttp\Exception\BadResponseException;
use Turbine\Workflow\Client\Http\GitlabHttpClient;
use Turbine\Workflow\Client\Http\SlackHttpClient;
use Turbine\Workflow\Configuration;

class SlackClient
{
    public function __construct(
        private SlackHttpClient $slackHttpClient,
        private Configuration $configuration
    ) {
    }

    public function sendMessageUsingWebhook(string $text): void
    {
        $this->slackHttpClient->post(
            $this->configuration->get(Configuration::SLACK_WEBHOOK_URL),
            ['text' => $text]
        );
    }
}
