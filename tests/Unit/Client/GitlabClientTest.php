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
        $configurationMock->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive(
                ['PROJECT_ID'],
                ['REPOSITORY'],
                ['GITLAB_API_URL'],
                ['BRANCH_DEVELOPMENT'],
            )
            ->willReturnOnConsecutiveCalls(
                '',
                'Project repository',
                'GITLAB_API_URL',
                'BRANCH_DEVELOPMENT',
            );
        $configurationMock->expects(self::once())
            ->method('getNonRemovableBranches')
            ->willReturn([]);

        $gitlabHttpClientMock = $this->createMock(GitlabHttpClient::class);
        $gitlabHttpClientMock->expects(self::once())
            ->method('post')
            ->with(
                'GITLAB_API_URL/projects/Project+repository/merge_requests',
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

    public function testCreateMergeRequestWithNonRemovableSourceBranch(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(
                ['PROJECT_ID'],
                ['GITLAB_API_URL'],
                ['BRANCH_DEVELOPMENT'],
            )
            ->willReturnOnConsecutiveCalls(
                '123',
                'GITLAB_API_URL',
                'BRANCH_DEVELOPMENT',
            );
        $configurationMock->expects(self::once())
            ->method('getNonRemovableBranches')
            ->willReturn(['source_branch']);

        $gitlabHttpClientMock = $this->createMock(GitlabHttpClient::class);
        $gitlabHttpClientMock->expects(self::once())
            ->method('post')
            ->with(
                'GITLAB_API_URL/projects/123/merge_requests',
                [
                    'source_branch' => 'source_branch',
                    'target_branch' => 'target_branch',
                    'approvals_before_merge' => 2,
                    'remove_source_branch' => false,
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

    public function testCreateMergeRequestAgainstDeploymentBranchDoesNotNeedApproval(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(5))
            ->method('get')
            ->withConsecutive(
                ['PROJECT_ID'],
                ['REPOSITORY'],
                ['GITLAB_API_URL'],
                ['BRANCH_DEVELOPMENT'],
                ['BRANCH_DEPLOYMENT'],
            )
            ->willReturnOnConsecutiveCalls(
                '',
                'Project repository',
                'GITLAB_API_URL',
                'BRANCH_DEVELOPMENT',
                'BRANCH_DEPLOYMENT',
            );
        $configurationMock->expects(self::once())
            ->method('getNonRemovableBranches')
            ->willReturn([]);

        $gitlabHttpClientMock = $this->createMock(GitlabHttpClient::class);
        $gitlabHttpClientMock->expects(self::once())
            ->method('post')
            ->with(
                'GITLAB_API_URL/projects/Project+repository/merge_requests',
                [
                    'source_branch' => 'BRANCH_DEVELOPMENT',
                    'target_branch' => 'BRANCH_DEPLOYMENT',
                    'approvals_before_merge' => 0,
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
                'source_branch' => 'BRANCH_DEVELOPMENT',
                'target_branch' => 'BRANCH_DEPLOYMENT',
            ]
        );

        self::assertEquals('url', $url);
    }

    public function testCreateMergeRequestFailedThrowsException(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive(
                ['PROJECT_ID'],
                ['REPOSITORY'],
                ['GITLAB_API_URL'],
                ['BRANCH_DEVELOPMENT'],
            )
            ->willReturnOnConsecutiveCalls(
                '',
                'Project repository',
                'GITLAB_API_URL',
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
                'GITLAB_API_URL/projects/Project+repository/merge_requests',
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

    public function testGetMergeRequestData(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(
                ['PROJECT_ID'],
                ['REPOSITORY'],
                ['GITLAB_API_URL']
            )
            ->willReturnOnConsecutiveCalls(
                '',
                'Project repository',
                'GITLAB_API_URL',
            );

        $gitlabHttpClientMock = $this->createMock(GitlabHttpClient::class);
        $gitlabHttpClientMock->expects(self::once())
            ->method('get')
            ->with(
                'GITLAB_API_URL/projects/Project+repository/merge_requests?source_branch=branch1&target_branch=branch2'
            )
            ->willReturn([[]]);

        $gitlabClient = new GitlabClient(
            $gitlabHttpClientMock,
            $configurationMock
        );

        $mergeRequestData = $gitlabClient->getMergeRequestData(
            [
                'source_branch' => 'branch1',
                'target_branch' => 'branch2',
            ]
        );

        self::assertSame([[]], $mergeRequestData);
    }

    public function testGetMergeRequestDataFailedThrowsException(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(
                ['PROJECT_ID'],
                ['REPOSITORY'],
                ['GITLAB_API_URL']
            )
            ->willReturnOnConsecutiveCalls(
                '',
                'Project repository',
                'GITLAB_API_URL',
            );

        $exceptionMock = $this->createMock(BadResponseException::class);

        $gitlabHttpClientMock = $this->createMock(GitlabHttpClient::class);
        $gitlabHttpClientMock->expects(self::once())
            ->method('get')
            ->with(
                'GITLAB_API_URL/projects/Project+repository/merge_requests?source_branch=branch1&target_branch=branch2'
            )
            ->willThrowException($exceptionMock);

        $gitlabClient = new GitlabClient(
            $gitlabHttpClientMock,
            $configurationMock
        );

        $this->expectException(BadResponseException::class);

        $gitlabClient->getMergeRequestData(
            [
                'source_branch' => 'branch1',
                'target_branch' => 'branch2',
            ]
        );
    }
}
