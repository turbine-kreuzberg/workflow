<?php

namespace Unit\Client\Http;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Client\Http\SlackHttpClient;

class SlackHttpClientTest extends TestCase
{
    public function testPostFunctionCallsGuzzleClient(): void
    {
        $container = [];
        $historyMock = Middleware::history($container);

        $responseMockHandler = new MockHandler(
            [
                new Response(200, [], json_encode([], JSON_THROW_ON_ERROR)),
                new Response(200, [], json_encode(['blub' => 'content'], JSON_THROW_ON_ERROR)),
            ]
        );

        $handlerStack = HandlerStack::create($responseMockHandler);
        $handlerStack->push($historyMock);

        $clientMock = new Client(['handler' => $handlerStack]);

        $slackHttpClient = new SlackHttpClient($clientMock);
        $slackHttpClient->post('webhook-url');
        $slackHttpClient->post('webhook-url', ['postOptions' => 'blub']);
        self::assertEquals('webhook-url', $container[0]['request']->getUri()->__toString());
        self::assertEquals(
            json_encode(['postOptions' => 'blub'], JSON_THROW_ON_ERROR),
            $container[1]['request']->getBody()->getContents()
        );
    }

    public function testPostFunctionCallsGuzzleClientWithoutAccessThrowsException(): void
    {
        $container = [];
        $historyMock = Middleware::history($container);

        $responseMockHandler = new MockHandler(
            [
                new BadResponseException('bad response', new Request('GET', 'test'), new Response(403)),
            ]
        );

        $handlerStack = HandlerStack::create($responseMockHandler);
        $handlerStack->push($historyMock);

        $clientMock = new Client(['handler' => $handlerStack]);

        $slackHttpClient = new SlackHttpClient($clientMock);
        $this->expectExceptionObject(
            new Exception(
                'Slack answered with 403 Forbidden: Please check your webhook url in your .env file.'
            )
        );
        $slackHttpClient->post('webhook-url');
    }

    public function testPostFunctionCallsGuzzleClientWithUnknownReasonErrorThrowsException(): void
    {
        $container = [];
        $historyMock = Middleware::history($container);

        $responseMockHandler = new MockHandler(
            [
                new BadResponseException('bad response', new Request('GET', 'test'), new Response(402)),
            ]
        );

        $handlerStack = HandlerStack::create($responseMockHandler);
        $handlerStack->push($historyMock);

        $clientMock = new Client(['handler' => $handlerStack]);

        $slackHttpClient = new SlackHttpClient($clientMock);
        $this->expectException(Exception::class);
        $slackHttpClient->post('webhook-url');
    }
}
