<?php

namespace Unit\Workflow\Model;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Client\GitClient;
use Turbine\Workflow\Client\GitlabClient;
use Turbine\Workflow\Client\JiraClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Transfers\JiraIssueTransfer;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\Model\MergeRequestCreator;
use Turbine\Workflow\Workflow\TicketIdProvider;

class MergeRequestCreatorTest extends TestCase
{
    public function testCreateMergeRequest(): void
    {
        $gitlabClientMock = $this->createMock(GitlabClient::class);
        $gitlabClientMock->expects(self::once())
            ->method('createMergeRequest')
            ->with(
                [
                    'source_branch' => 'currentBranch',
                    'target_branch' => 'developmentBranch',
                    'title' => '[ABC-134] summary',
                    'description' => "commit message\nCloses ABC-134\n",
                    'remove_source_branch' => true,
                    'labels' => 'bug,task,platform',
                    'approvals_before_merge' => 2,
                ]
            )
            ->willReturn('mergeRequestUrl');

        $jiraIssueTransfer = new JiraIssueTransfer();
        $jiraIssueTransfer->key = 'ABC-134';
        $jiraIssueTransfer->summary = 'summary';
        $jiraIssueTransfer->labels = ['bug', 'task', 'platform'];

        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('getIssue')
            ->with('ABC-134')
            ->willReturn($jiraIssueTransfer);

        $gitClientMock = $this->createMock(GitClient::class);
        $gitClientMock->expects(self::once())
            ->method('getCurrentBranchName')
            ->willReturn('currentBranch');

        $gitClientMock->expects(self::once())
            ->method('getGitLog')
            ->willReturn('commit message');

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('get')
            ->with('BRANCH_DEVELOPMENT')
            ->willReturn('developmentBranch');

        $ticketIdProviderMock = $this->createMock(TicketIdProvider::class);
        $ticketIdProviderMock->expects(self::once())
            ->method('extractTicketIdFromCurrentBranch')
            ->willReturn('ABC-134');

        $issueUpdaterMock = $this->createMock(IssueUpdater::class);
        $issueUpdaterMock->expects(self::once())
            ->method('moveIssueToStatus')
            ->with('ABC-134', 'code review');

        $mergeRequestCreator = new MergeRequestCreator(
            $gitlabClientMock,
            $jiraClientMock,
            $gitClientMock,
            $configurationMock,
            $ticketIdProviderMock,
            $issueUpdaterMock
        );

        self::assertEquals('mergeRequestUrl', $mergeRequestCreator->createForCurrentBranch());
    }
}
