<?php

namespace Unit\Client;

use GuzzleHttp\Exception\BadResponseException;
use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Client\Http\SlackHttpClient;
use Turbine\Workflow\Client\SlackClient;
use Turbine\Workflow\Configuration;

class SlackClientTest extends TestCase
{
    public function testSendMessageUsingWebhook(): void
    {
        $slackHttpClientMock = $this->createMock(SlackHttpClient::class);
        $slackHttpClientMock
            ->expects(self::once())
            ->method('post')
            ->with('webhook-url', ['text' => 'some Message']);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('get')
            ->with('SLACK_WEBHOOK_URL')
            ->willReturn('webhook-url');

        $slackClient = new SlackClient(
            $slackHttpClientMock,
            $configurationMock
        );

        $slackClient->sendMessageUsingWebhook('some Message');
    }

    public function testSendMessageUsingWebhookFailedThrowsException(): void
    {
        $exceptionMock = $this->createMock(BadResponseException::class);

        $slackHttpClientMock = $this->createMock(SlackHttpClient::class);
        $slackHttpClientMock
            ->expects(self::once())
            ->method('post')
            ->with('webhook-url', ['text' => 'some Message'])
            ->willThrowException($exceptionMock);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('get')
            ->with('SLACK_WEBHOOK_URL')
            ->willReturn('webhook-url');

        $slackClient = new SlackClient(
            $slackHttpClientMock,
            $configurationMock
        );

        $this->expectException(BadResponseException::class);

        $slackClient->sendMessageUsingWebhook('some Message');
    }
}
