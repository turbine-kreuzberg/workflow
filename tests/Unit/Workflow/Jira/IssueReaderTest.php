<?php

namespace Unit\Workflow\Jira;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Client\JiraClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Exception\JiraNoWorklogException;
use Turbine\Workflow\Transfers\JiraIssueTransfer;
use Turbine\Workflow\Transfers\JiraIssueTransferCollection;
use Turbine\Workflow\Transfers\JiraWorklogEntryTransfer;
use Turbine\Workflow\Transfers\JiraWorklogsTransfer;
use Turbine\Workflow\Workflow\Jira\IssueReader;

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
        $configurationMock = $this->createMock(Configuration::class);

        $issueReader = new IssueReader($jiraClientMock, $configurationMock);

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
        $configurationMock = $this->createMock(Configuration::class);

        $issueReader = new IssueReader($jiraClientMock, $configurationMock);

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

        $configurationMock = $this->createMock(Configuration::class);

        $issueReader = new IssueReader($jiraClientMock, $configurationMock);

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

        $configurationMock = $this->createMock(Configuration::class);

        $issueReader = new IssueReader($jiraClientMock, $configurationMock);

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

        $configurationMock = $this->createMock(Configuration::class);

        $issueReader = new IssueReader($jiraClientMock, $configurationMock);

        $issuesCollection = $issueReader->getIssues([]);
        self::assertEquals(new JiraIssueTransferCollection([]), $issuesCollection);
    }

    public function testGetIssuesReturnsIssueCollectionOfReceivedJiraIssues(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('getIssue')
            ->willReturn(new JiraIssueTransfer());

        $configurationMock = $this->createMock(Configuration::class);

        $issueReader = new IssueReader($jiraClientMock, $configurationMock);

        $issuesCollection = $issueReader->getIssues(['BCM-12']);
        self::assertEquals(new JiraIssueTransferCollection([new JiraIssueTransfer()]), $issuesCollection);
    }

    public function testGetIssueReturnsJiraIssueTransfer(): void
    {
        $testJiraIssueTransfer = new JiraIssueTransfer();
        $testJiraIssueTransfer->key = 'KEY-123';

        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('getIssue')
            ->with('KEY-123')
            ->willReturn($testJiraIssueTransfer);

        $configurationMock = $this->createMock(Configuration::class);

        $issueReader = new IssueReader($jiraClientMock, $configurationMock);

        $returnJiraIssueTransfer = $issueReader->getIssue('KEY-123');
        self::assertEquals($testJiraIssueTransfer, $returnJiraIssueTransfer);
    }

    public function testGetIssueCalledWithNumberReturnsJiraIssueTransfer(): void
    {
        $testJiraIssueTransfer = new JiraIssueTransfer();
        $testJiraIssueTransfer->key = 'KEY-123';

        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('getIssue')
            ->with('KEY-123')
            ->willReturn($testJiraIssueTransfer);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('get')
            ->with('JIRA_PROJECT_KEY')
            ->willReturn('KEY');

        $issueReader = new IssueReader($jiraClientMock, $configurationMock);

        $returnJiraIssueTransfer = $issueReader->getIssue('123');
        self::assertEquals($testJiraIssueTransfer, $returnJiraIssueTransfer);
    }

    public function testGetTimeSpentToday(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('getTimeSpentByDate')
            ->willReturn(3600.0);

        $configurationMock = $this->createMock(Configuration::class);

        $issueReader = new IssueReader($jiraClientMock, $configurationMock);

        self::assertEquals(3600.0, $issueReader->getTimeSpentToday());
    }

    public function testGetCompleteWorklog(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('getCompleteWorklogByDate')
            ->willReturn(new JiraWorklogsTransfer());

        $configurationMock = $this->createMock(Configuration::class);

        $issueReader = new IssueReader($jiraClientMock, $configurationMock);

        self::assertEquals(new JiraWorklogsTransfer(), $issueReader->getCompleteWorklog());
    }

    public function testGetIssueTransitions(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('getIssueTransitions')
            ->with('issueKey')
            ->willReturn([]);

        $configurationMock = $this->createMock(Configuration::class);

        $issueReader = new IssueReader($jiraClientMock, $configurationMock);

        self::assertEquals([], $issueReader->getIssueTransitions('issueKey'));
    }
}
