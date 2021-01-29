<?php

namespace Unit\Client\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Workflow\Client\Http\AtlassianHttpClient;
use Workflow\Configuration;

class AtlassianHttpClientTest extends TestCase
{
    public function testGetFunctionCallsGuzzleClient(): void
    {
        $container = [];
        $historyMock = Middleware::history($container);

        $responseMockHandler = new MockHandler(
            [
            new Response(200, [], json_encode([], JSON_THROW_ON_ERROR)),
            ]
        );

        $handlerStack = HandlerStack::create($responseMockHandler);
        $handlerStack->push($historyMock);

        $clientMock = new Client(['handler' => $handlerStack]);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(2))
            ->method('getConfiguration')
            ->withConsecutive(
                ['JIRA_USERNAME'],
                ['JIRA_PASSWORD'],
            )
            ->willReturnOnConsecutiveCalls('username', 'password');
        $atlassianHttpClient = new AtlassianHttpClient(
            $configurationMock,
            $clientMock
        );
        self::assertEquals([], $atlassianHttpClient->get('url'));
        self::assertEquals('url', $container[0]['request']->getUri()->__toString());
        self::assertEquals(['username', 'password'], $container[0]['options']['auth']);
    }

    public function testPostFunctionCallsGuzzleClient(): void
    {
        $container = [];
        $historyMock = Middleware::history($container);

        $responseMockHandler = new MockHandler(
            [
            new Response(200, [], json_encode([], JSON_THROW_ON_ERROR)),
            ]
        );

        $handlerStack = HandlerStack::create($responseMockHandler);
        $handlerStack->push($historyMock);

        $clientMock = new Client(['handler' => $handlerStack]);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(2))
            ->method('getConfiguration')
            ->withConsecutive(
                ['JIRA_USERNAME'],
                ['JIRA_PASSWORD'],
            )
            ->willReturnOnConsecutiveCalls('username', 'password');
        $atlassianHttpClient = new AtlassianHttpClient(
            $configurationMock,
            $clientMock
        );

        self::assertEquals([], $atlassianHttpClient->post('url', ['postOptions' => 'blub']));
        self::assertEquals('url', $container[0]['request']->getUri()->__toString());
        self::assertEquals(['username', 'password'], $container[0]['options']['auth']);
        self::assertEquals(
            json_encode(['postOptions' => 'blub'], JSON_THROW_ON_ERROR),
            $container[0]['request']->getBody()->getContents()
        );
    }

    public function testPostFunctionWithInvalidJsonResponseReturnsAnEmptyArray(): void
    {
        $mockHandler = new MockHandler(
            [
            new Response(200, [], 'invalid_json'),
            ]
        );

        $clientMock = new Client(['handler' => $mockHandler]);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(2))
            ->method('getConfiguration')
            ->withConsecutive(
                ['JIRA_USERNAME'],
                ['JIRA_PASSWORD'],
            )
            ->willReturnOnConsecutiveCalls('username', 'password');
        $atlassianHttpClient = new AtlassianHttpClient(
            $configurationMock,
            $clientMock
        );

        self::assertEquals([], $atlassianHttpClient->post('url', ['postOptions']));
    }

    public function testPutFunctionCallsGuzzleClient(): void
    {
        $container = [];
        $historyMock = Middleware::history($container);

        $responseMockHandler = new MockHandler(
            [
            new Response(200, [], json_encode([], JSON_THROW_ON_ERROR)),
            ]
        );

        $handlerStack = HandlerStack::create($responseMockHandler);
        $handlerStack->push($historyMock);

        $clientMock = new Client(['handler' => $handlerStack]);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(2))
            ->method('getConfiguration')
            ->withConsecutive(
                ['JIRA_USERNAME'],
                ['JIRA_PASSWORD'],
            )
            ->willReturnOnConsecutiveCalls('username', 'password');
        $atlassianHttpClient = new AtlassianHttpClient(
            $configurationMock,
            $clientMock
        );

        $response = $atlassianHttpClient->put('url', ['putOptions' => 'blub']);

        self::assertEquals([], $response);
        self::assertEquals('url', $container[0]['request']->getUri()->__toString());
        self::assertEquals(['username', 'password'], $container[0]['options']['auth']);
        self::assertEquals(
            json_encode(['putOptions' => 'blub'], JSON_THROW_ON_ERROR),
            $container[0]['request']->getBody()->getContents()
        );
    }

    public function testPutFunctionWithInvalidJsonResponseReturnsAnEmptyArray(): void
    {
        $mockHandler = new MockHandler(
            [
            new Response(200, [], 'invalid_json'),
            ]
        );

        $clientMock = new Client(['handler' => $mockHandler]);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(2))
            ->method('getConfiguration')
            ->withConsecutive(
                ['JIRA_USERNAME'],
                ['JIRA_PASSWORD'],
            )
            ->willReturnOnConsecutiveCalls('username', 'password');
        $atlassianHttpClient = new AtlassianHttpClient(
            $configurationMock,
            $clientMock
        );

        self::assertEquals([], $atlassianHttpClient->put('url', ['putOptions']));
    }
}
