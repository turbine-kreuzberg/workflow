<?php

namespace Unit\Workflow\Jira;

use PHPUnit\Framework\TestCase;
use Workflow\Client\JiraClient;
use Workflow\Workflow\Jira\IssueUpdater;

class IssueUpdaterTest extends TestCase
{
    public function testBookTime(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('bookTime')
            ->with(
                'issue', [
                'comment' => 'comment',
                'started' => 'now',
                'timeSpentSeconds' => 60,
                ]
            );
        
        $issueUpdater = new IssueUpdater($jiraClientMock);

        $issueUpdater->bookTime(
            'issue',
            'comment',
            1,
            'now'
        );
    }
}
