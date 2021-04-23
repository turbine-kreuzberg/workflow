<?php

namespace Unit\Workflow\Provider;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Client\GitClient;
use Turbine\Workflow\Client\JiraClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Transfers\JiraIssueTransfer;
use Turbine\Workflow\Workflow\Provider\BranchNameProvider;

class BranchNameProviderTest extends TestCase
{
    public function testGetBranchNameFromTicketNumber(): void
    {
        $testJiraIssueTransfer = new JiraIssueTransfer();
        $testJiraIssueTransfer->key = 'ABC-145';
        $testJiraIssueTransfer->summary = 'Jira Issue summary';

        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('getIssue')
            ->with('ABC-145')
            ->willReturn($testJiraIssueTransfer);

        $gitClientMock = $this->createMock(GitClient::class);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->method('get')
            ->with('JIRA_PROJECT_KEY')
            ->willReturn('ABC');

        $branchNameProvider = new BranchNameProvider(
            $jiraClientMock,
            $gitClientMock,
            $configurationMock
        );

        $branchName = $branchNameProvider->getBranchNameFromTicket('145');
        self::assertSame('ABC-145-jira-issue-summary', $branchName);
    }

    public function testGetCurrentBranchName(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $gitClientMock = $this->createMock(GitClient::class);
        $gitClientMock->expects(self::once())->method('getCurrentBranchName')->willReturn('current-branch');
        $configurationMock = $this->createMock(Configuration::class);

        $branchNameProvider = new BranchNameProvider(
            $jiraClientMock,
            $gitClientMock,
            $configurationMock
        );

        $currentBranchName = $branchNameProvider->getCurrentBranchName();

        self::assertSame('current-branch', $currentBranchName);
    }
}
