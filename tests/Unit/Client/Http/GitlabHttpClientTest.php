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
use Turbine\Workflow\Configuration;

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

        $configurationMock = $this->createMock(Configuration::class);

        $gitlabHttpClient = new GitlabHttpClient($configurationMock, $clientMock);
        self::assertEquals([], $gitlabHttpClient->get('gitlab-url'));
        self::assertEquals(['blub' => 'content'], $gitlabHttpClient->get('gitlab-url'));
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

        $configurationMock = $this->createMock(Configuration::class);

        $gitlabHttpClient = new GitlabHttpClient($configurationMock, $clientMock);
        $this->expectExceptionObject(
            new Exception(
                'Gitlab answered with 401 Unauthorized: Please check your personal access token in your .env file.'
            )
        );
        self::assertEquals([], $gitlabHttpClient->get('gitlab-url'));
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

        $configurationMock = $this->createMock(Configuration::class);

        $gitlabHttpClient = new GitlabHttpClient($configurationMock, $clientMock);
        $this->expectException(Exception::class);
        self::assertEquals([], $gitlabHttpClient->get('gitlab-url'));
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

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(2))
            ->method('get')
            ->with('GITLAB_PERSONAL_ACCESS_TOKEN')
            ->willReturn('gitlab personal token');

        $gitlabHttpClient = new GitlabHttpClient($configurationMock, $clientMock);
        self::assertEquals([], $gitlabHttpClient->post('gitlab-url'));
        self::assertEquals(
            ['gitlab personal token'],
            $container[0]['request']->getHeaders()['Private-Token']
        );

        self::assertEquals(['blub' => 'content'], $gitlabHttpClient->post('gitlab-url', ['postOptions' => 'blub']));
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

        $configurationMock = $this->createMock(Configuration::class);

        $gitlabHttpClient = new GitlabHttpClient($configurationMock, $clientMock);
        $this->expectExceptionObject(
            new Exception(
                'Gitlab answered with 401 Unauthorized: Please check your personal access token in your .env file.'
            )
        );
        self::assertEquals([], $gitlabHttpClient->post('gitlab-url'));
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

        $configurationMock = $this->createMock(Configuration::class);

        $gitlabHttpClient = new GitlabHttpClient($configurationMock, $clientMock);
        $this->expectException(Exception::class);
        self::assertEquals([], $gitlabHttpClient->post('gitlab-url'));
    }
}
