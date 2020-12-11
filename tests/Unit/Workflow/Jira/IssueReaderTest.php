<?php

namespace Unit\Workflow\Jira;

use PHPUnit\Framework\TestCase;
use Workflow\Client\JiraClient;
use Workflow\Exception\JiraNoWorklogException;
use Workflow\Transfers\JiraIssueTransfer;
use Workflow\Transfers\JiraIssueTransferCollection;
use Workflow\Transfers\JiraWorklogEntryTransfer;
use Workflow\Workflow\Jira\IssueReader;

class IssueReaderTest extends TestCase
{
    private const ISSUE_TIME_SPENT_1 = 23;
    private const ISSUE_COMMENT_1 = 'work-log-comment';
    private const ISSUE_AUTHOR_NAME_1 = 'Author Name';

    private const ISSUE_TIME_SPENT_2 = 46;
    private const ISSUE_COMMENT_2 = 'work-log-comment-2';
    private const ISSUE_AUTHOR_NAME_2 = 'Another Author Name';

    private const ISSUE = 'BCM-12';

    public function testGetLastWorkLogReceivingEmptyWorklogResponseThrowsException(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('getWorkLog')
            ->with(self::ISSUE)
            ->willReturn([]);

        $issueReader = new IssueReader($jiraClientMock);

        $this->expectException(JiraNoWorklogException::class);
        $issueReader->getLastTicketWorklog(self::ISSUE);
    }

    public function testGetLastWorkLogReceivingWorklogResponseWithNoEntriesThrowsException(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('getWorkLog')
            ->with(self::ISSUE)
            ->willReturn(['total' => 0]);

        $issueReader = new IssueReader($jiraClientMock);

        $this->expectException(JiraNoWorklogException::class);
        $issueReader->getLastTicketWorklog(self::ISSUE);
    }

    public function testGetLastWorkLogReceivingWorklogResponseWithOneEntryReturnsEntry(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('getWorkLog')
            ->willReturn(
                [
                'total' => 1,
                'worklogs' => [
                    [
                        'comment' => self::ISSUE_COMMENT_1,
                        'author' => ['displayName' => self::ISSUE_AUTHOR_NAME_1],
                        'timeSpentSeconds' => self::ISSUE_TIME_SPENT_1,
                    ],
                ],
                ]
            );

        $issueReader = new IssueReader($jiraClientMock);

        $lastTicketWorkLog = $issueReader->getLastTicketWorklog(self::ISSUE);

        $jiraWorklogEntryTransfer = new JiraWorklogEntryTransfer();
        $jiraWorklogEntryTransfer->author = self::ISSUE_AUTHOR_NAME_1;
        $jiraWorklogEntryTransfer->comment = self::ISSUE_COMMENT_1;
        $jiraWorklogEntryTransfer->timeSpentSeconds = self::ISSUE_TIME_SPENT_1;

        self::assertEquals($jiraWorklogEntryTransfer, $lastTicketWorkLog);
    }

    public function testGetLastWorkLogReceivingWorklogResponseWithMultipleEntriesReturnsLastEntry(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('getWorkLog')
            ->with(self::ISSUE)
            ->willReturn(
                [
                'total' => 2,
                'worklogs' => [
                    [
                        'comment' => self::ISSUE_COMMENT_1,
                        'author' => ['displayName' => self::ISSUE_AUTHOR_NAME_1],
                        'timeSpentSeconds' => self::ISSUE_TIME_SPENT_1
                    ],
                    [
                        'comment' => self::ISSUE_COMMENT_2,
                        'author' => ['displayName' => self::ISSUE_AUTHOR_NAME_2],
                        'timeSpentSeconds' => self::ISSUE_TIME_SPENT_2,
                    ],
                ],
                ]
            );

        $issueReader = new IssueReader($jiraClientMock);

        $lastTicketWorkLog = $issueReader->getLastTicketWorklog(self::ISSUE);

        $jiraWorklogEntryTransfer = new JiraWorklogEntryTransfer();
        $jiraWorklogEntryTransfer->author = self::ISSUE_AUTHOR_NAME_2;
        $jiraWorklogEntryTransfer->comment = self::ISSUE_COMMENT_2;
        $jiraWorklogEntryTransfer->timeSpentSeconds = self::ISSUE_TIME_SPENT_2;

        self::assertEquals($jiraWorklogEntryTransfer, $lastTicketWorkLog);
    }

    public function testGetIssuesReturnsEmptyIssueCollectionForEmptyIssueList(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::never())
            ->method('getIssue');

        $issueReader = new IssueReader($jiraClientMock);

        $issuesCollection = $issueReader->getIssues([]);
        self::assertEquals(new JiraIssueTransferCollection([]), $issuesCollection);
    }

    public function testGetIssuesReturnsIssueCollectionOfReceivedJiraIssues(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('getIssue')
            ->willReturn(new JiraIssueTransfer());

        $issueReader = new IssueReader($jiraClientMock);

        $issuesCollection = $issueReader->getIssues(['BCM-12']);
        self::assertEquals(new JiraIssueTransferCollection([new JiraIssueTransfer()]), $issuesCollection);
    }
}