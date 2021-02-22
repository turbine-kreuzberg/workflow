<?php

namespace Unit\Workflow\Model;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Client\GitClient;
use Turbine\Workflow\Client\JiraClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\Model\WorkOnTicket;

class WorkOnTicketTest extends TestCase
{
    public function testWorkOnTicket(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['JIRA_PROJECT_KEY'], ['BRANCH_DEVELOPMENT'])
            ->willReturnOnConsecutiveCalls('ABC', 'develop');

        $gitClientMock = $this->createMock(GitClient::class);
        $gitClientMock->expects(self::once())
            ->method('createBranchOnTopOf')
            ->with('develop', 'hallo-ballo');

        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('assignJiraIssueToUser')
            ->with('ABC-2344');

        $issueUpdaterMock = $this->createMock(IssueUpdater::class);
        $issueUpdaterMock->expects(self::once())
            ->method('moveIssueToStatus')
            ->with('ABC-2344', 'in progress');

        $workOnTicket = new WorkOnTicket(
            $jiraClientMock,
            $gitClientMock,
            $configurationMock,
            $issueUpdaterMock
        );

        $workOnTicket->workOnTicket('2344', 'hallo-ballo');
    }
}