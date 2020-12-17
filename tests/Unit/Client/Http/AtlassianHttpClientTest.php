<?php

namespace Unit\Client\Http;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Workflow\Client\Http\AtlassianHttpClient;
use Workflow\Configuration;

class AtlassianHttpClientTest extends TestCase
{
    public function testGetFunctionCallsGuzzleClient(): void
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->expects(self::once())
            ->method('getContents')
            ->willReturn('{}');

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn($streamMock);

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects(self::once())
            ->method('__call')
            ->with('get', ['url', ['auth' => ['username', 'password']]])
            ->willReturn($responseMock);

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
    }

    public function testPostFunctionCallsGuzzleClient(): void
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->expects(self::once())
            ->method('getContents')
            ->willReturn('{}');

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn($streamMock);

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects(self::once())
            ->method('__call')
            ->with(
                'post',
                ['url', ['json' => ['postOptions'], 'auth' => ['username', 'password']]]
            )
            ->willReturn($responseMock);

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

    public function testPostFunctionWithInvalidJsonResponseReturnsAnEmptyArray(): void
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->expects(self::once())
            ->method('getContents')
            ->willReturn('');

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn($streamMock);

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects(self::once())
            ->method('__call')
            ->with(
                'post',
                ['url', ['json' => ['postOptions'], 'auth' => ['username', 'password']]]
            )
            ->willReturn($responseMock);

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
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->expects(self::once())
            ->method('getContents')
            ->willReturn('{}');

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn($streamMock);

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects(self::once())
            ->method('__call')
            ->with(
                'put',
                ['url', ['json' => ['putOptions'], 'auth' => ['username', 'password']]]
            )
            ->willReturn($responseMock);

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

    public function testPutFunctionWithInvalidJsonResponseReturnsAnEmptyArray(): void
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->expects(self::once())
            ->method('getContents')
            ->willReturn('');

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn($streamMock);

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects(self::once())
            ->method('__call')
            ->with(
                'put',
                ['url', ['json' => ['putOptions'], 'auth' => ['username', 'password']]]
            )
            ->willReturn($responseMock);

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
