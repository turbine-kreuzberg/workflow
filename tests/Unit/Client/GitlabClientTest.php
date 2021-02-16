<?php

namespace Unit\Client;

use GuzzleHttp\Exception\BadResponseException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Turbine\Workflow\Client\GitlabClient;
use Turbine\Workflow\Client\Http\GitlabHttpClient;
use Turbine\Workflow\Configuration;

class GitlabClientTest extends TestCase
{
    public function testCreateMergeRequest(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(3))
            ->method('getConfiguration')
            ->withConsecutive(
                ['GITLAB_API_URL'],
                ['REPOSITORY'],
                ['BRANCH_DEVELOPMENT'],
            )
            ->willReturnOnConsecutiveCalls(
                'GITLAB_API_URL',
                'Project repository',
                'BRANCH_DEVELOPMENT',
            );
        $configurationMock->expects(self::once())
            ->method('getNonRemovableBranches')
            ->willReturn([]);

        $gitlabHttpClientMock = $this->createMock(GitlabHttpClient::class);
        $gitlabHttpClientMock->expects(self::once())
            ->method('post')
            ->with(
                'GITLAB_API_URLprojects/Project+repository/merge_requests',
                [
                    'source_branch' => 'source_branch',
                    'target_branch' => 'target_branch',
                    'approvals_before_merge' => 2,
                    'remove_source_branch' => true,
                ]
            )
            ->willReturn(['web_url' => 'url']);

        $gitlabClient = new GitlabClient(
            $gitlabHttpClientMock,
            $configurationMock
        );

        $url = $gitlabClient->createMergeRequest(
            [
                'source_branch' => 'source_branch',
                'target_branch' => 'target_branch',
            ]
        );

        self::assertEquals('url', $url);
    }


    public function testCreateMergeRequestFailedThrowsException(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(3))
            ->method('getConfiguration')
            ->withConsecutive(
                ['GITLAB_API_URL'],
                ['REPOSITORY'],
                ['BRANCH_DEVELOPMENT'],
            )
            ->willReturnOnConsecutiveCalls(
                'GITLAB_API_URL',
                'Project repository',
                'BRANCH_DEVELOPMENT',
            );
        $configurationMock->expects(self::once())
            ->method('getNonRemovableBranches')
            ->willReturn([]);

        $exceptionMock = $this->createMock(BadResponseException::class);

        $gitlabHttpClientMock = $this->createMock(GitlabHttpClient::class);
        $gitlabHttpClientMock->expects(self::once())
            ->method('post')
            ->with(
                'GITLAB_API_URLprojects/Project+repository/merge_requests',
                [
                    'source_branch' => 'source_branch',
                    'target_branch' => 'target_branch',
                    'approvals_before_merge' => 2,
                    'remove_source_branch' => true,
                ]
            )
            ->willThrowException($exceptionMock);

        $gitlabClient = new GitlabClient(
            $gitlabHttpClientMock,
            $configurationMock
        );

        $this->expectException(BadResponseException::class);

        $gitlabClient->createMergeRequest(
            [
                'source_branch' => 'source_branch',
                'target_branch' => 'target_branch',
            ]
        );
    }
}
