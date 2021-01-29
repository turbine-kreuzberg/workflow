<?php

namespace Unit\Workflow\Jira;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Client\JiraClient;
use Turbine\Workflow\Exception\JiraStateNotFoundException;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;

class IssueUpdaterTest extends TestCase
{
    public function testBookTimeConvertsToHours(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('bookTime')
            ->with(
                'issue', [
                'comment' => 'comment',
                'started' => 'now',
                'timeSpentSeconds' => 5400,
                ]
            );

        $issueUpdater = new IssueUpdater($jiraClientMock);

        $issueUpdater->bookTime(
            'issue',
            'comment',
            1.5,
            'now'
        );
    }

    public function testBookTimeInMinutes(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('bookTime')
            ->with(
                'issue', [
                    'comment' => 'comment',
                    'started' => 'now',
                    'timeSpentSeconds' => 930,
                ]
            );

        $issueUpdater = new IssueUpdater($jiraClientMock);

        $bookedTime = $issueUpdater->bookTime(
            'issue',
            'comment',
            15.5,
            'now'
        );

        self::assertEquals(15.5, $bookedTime);
    }

    public function testUseHttpClientToMoveIssueToStatus(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('getIssueTransitions')
            ->with('BCM-12')
            ->willReturn(
                [
                    'transitions' => [
                        [
                            'to' => [
                                'name' => 'unwantedState',
                            ],
                            'id' => 'unwantedTransitionId',
                        ],
                        [
                            'to' => [
                                'name' => 'targetState',
                            ],
                            'id' => 'transitionId',
                        ],
                    ],
                ]
            );

        $jiraClientMock->expects(self::once())
            ->method('transitionJiraIssue')
            ->with(
                'BCM-12',
                'transitionId'
            );

        $issueUpdater = new IssueUpdater($jiraClientMock);

        $issueUpdater->moveIssueToStatus('BCM-12', 'targetState');
    }

    public function testMoveTicketToStatusThrowsExceptionForNotFoundTargetState(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('getIssueTransitions')
            ->with('BCM-12')
            ->willReturn(
                [
                    'transitions' => [
                        [
                            'to' => [
                                'name' => 'targetState',
                            ],
                            'id' => 'transitionId',
                        ],
                    ],
                ]
            );

        $jiraClientMock->expects(self::never())
            ->method('transitionJiraIssue');

        $issueUpdater = new IssueUpdater(
            $jiraClientMock,
        );

        $this->expectException(JiraStateNotFoundException::class);
        $this->expectExceptionMessage('target state "unknownState" not available for issue');

        $issueUpdater->moveIssueToStatus('BCM-12', 'unknownState');
    }
}
