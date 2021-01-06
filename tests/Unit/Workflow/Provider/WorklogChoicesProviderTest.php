<?php

namespace Unit\Workflow\Provider;

use PHPUnit\Framework\TestCase;
use Workflow\Transfers\JiraWorklogEntryTransfer;
use Workflow\Workflow\Jira\IssueReader;
use Workflow\Workflow\Provider\CommitMessageProvider;
use Workflow\Workflow\Provider\WorklogChoicesProvider;

class WorklogChoicesProviderTest extends TestCase
{
    public function testProvideWorklogChoices(): void
    {
        $jiraWorklogEntryTransfer = new JiraWorklogEntryTransfer;
        $jiraWorklogEntryTransfer->author = 'test Author';
        $jiraWorklogEntryTransfer->comment = 'jira worklog';
        $jiraWorklogEntryTransfer->timeSpentSeconds = 1000;

        $issueReaderMock = $this->createMock(IssueReader::class);
        $issueReaderMock->expects(self::once())
            ->method('getLastTicketWorklog')
            ->with('issue')
            ->willReturn($jiraWorklogEntryTransfer);

        $commitMessageProviderMock = $this->createMock(CommitMessageProvider::class);
        $commitMessageProviderMock->expects(self::once())
            ->method('getLastCommitMessage')
            ->willReturn('last git Commit');

        $worklogChoicesProvider = new WorklogChoicesProvider($issueReaderMock, $commitMessageProviderMock);

        self::assertEquals(
            [
                'jira worklog',
                'last git Commit',
            ],
            $worklogChoicesProvider->provide('issue')
        );
    }
}
