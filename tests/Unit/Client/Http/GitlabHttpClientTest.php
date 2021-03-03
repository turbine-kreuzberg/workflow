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
use PHPStan\Testing\TestCase;
use Turbine\Workflow\Client\Http\GitlabHttpClient;

class GitlabHttpClientTest extends TestCase
{
    public function testGetFunctionCallsGuzzleClient(): void
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

        $atlassianHttpClient = new GitlabHttpClient($clientMock);
        self::assertEquals([], $atlassianHttpClient->get('gitlab-url'));
        self::assertEquals(['blub' => 'content'], $atlassianHttpClient->get('gitlab-url'));
        self::assertEquals('gitlab-url', $container[0]['request']->getUri()->__toString());
    }

    public function testGetFunctionCallsGuzzleClientWithoutAccessThrowsException(): void
    {
        $container = [];
        $historyMock = Middleware::history($container);

        $responseMockHandler = new MockHandler(
            [
                new BadResponseException('bad response', new Request('GET', 'test'), new Response(401)),
            ]
        );

        $handlerStack = HandlerStack::create($responseMockHandler);
        $handlerStack->push($historyMock);

        $clientMock = new Client(['handler' => $handlerStack]);

        $atlassianHttpClient = new GitlabHttpClient($clientMock);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Gitlab answered with 401 Unauthorized: Please check your personal access token in your .env file.'
        );
        self::assertEquals([], $atlassianHttpClient->get('gitlab-url'));
    }

    public function testGetFunctionCallsGuzzleClientWithUnknownReasonErrorThrowsException(): void
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

        $atlassianHttpClient = new GitlabHttpClient($clientMock);
        $this->expectException(Exception::class);
        self::assertEquals([], $atlassianHttpClient->get('gitlab-url'));
    }

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

        $atlassianHttpClient = new GitlabHttpClient($clientMock);
        self::assertEquals([], $atlassianHttpClient->post('gitlab-url'));
        self::assertEquals(['blub' => 'content'], $atlassianHttpClient->post('gitlab-url', ['postOptions' => 'blub']));
        self::assertEquals('gitlab-url', $container[0]['request']->getUri()->__toString());
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
                new BadResponseException('bad response', new Request('GET', 'test'), new Response(401)),
            ]
        );

        $handlerStack = HandlerStack::create($responseMockHandler);
        $handlerStack->push($historyMock);

        $clientMock = new Client(['handler' => $handlerStack]);

        $atlassianHttpClient = new GitlabHttpClient($clientMock);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Gitlab answered with 401 Unauthorized: Please check your personal access token in your .env file.'
        );
        self::assertEquals([], $atlassianHttpClient->post('gitlab-url'));
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

        $atlassianHttpClient = new GitlabHttpClient($clientMock);
        $this->expectException(Exception::class);
        self::assertEquals([], $atlassianHttpClient->post('gitlab-url'));
    }
}