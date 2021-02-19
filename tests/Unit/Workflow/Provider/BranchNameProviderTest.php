<?php

namespace Unit\Workflow\Provider;

use PHPUnit\Framework\TestCase;
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
            ->willReturn($testJiraIssueTransfer);
        $configurationMock = $this->createMock(Configuration::class);

        $branchNameProvider = new BranchNameProvider(
            $jiraClientMock,
            $configurationMock
        );

        $branchName = $branchNameProvider->getBranchNameFromTicket('ABC-145');
        self::assertSame('ABC-145-jira-issue-summary', $branchName);
    }
}