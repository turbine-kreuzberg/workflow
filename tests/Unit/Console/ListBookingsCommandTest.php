<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Console\ListBookingsCommand;
use Turbine\Workflow\Transfers\JiraWorklogEntryCollectionTransfer;
use Turbine\Workflow\Transfers\JiraWorklogEntryTransfer;
use Turbine\Workflow\Transfers\JiraWorklogsTransfer;
use Turbine\Workflow\Workflow\Jira\IssueReader;
use Turbine\Workflow\Workflow\WorkflowFactory;

class ListBookingsCommandTest extends TestCase
{
    public function testListBookingsWithNoWorklogs(): void
    {
        $issueReaderMock = $this->createMock(IssueReader::class);
        $jiraWorklogsTransfer = new JiraWorklogsTransfer();
        $jiraWorklogsTransfer->totalSpentTime = 0;
        $jiraWorklogsTransfer->jiraWorklogEntryCollection = new JiraWorklogEntryCollectionTransfer([]);
        $issueReaderMock->expects(self::once())
            ->method('getCompleteWorklog')
            ->willReturn($jiraWorklogsTransfer);

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('success')
            ->with('Daily Bookings: 00:00');

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);

        $listBookingsCommand = new ListBookingsCommand(
            jiraIssueReader: $issueReaderMock,
            workflowFactory: $workflowFactoryMock
        );

        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        self::assertEquals(0, $listBookingsCommand->run($inputMock, $outputMock));
    }

    public function testListBookingsWithWorklogs(): void
    {
        $issueReaderMock = $this->createMock(IssueReader::class);
        $jiraWorklogsTransfer = new JiraWorklogsTransfer();
        $jiraWorklogsTransfer->totalSpentTime = 3600;

        $jiraWorklogEntryTransfer = new JiraWorklogEntryTransfer();
        $jiraWorklogEntryTransfer->key = 'Ticket one';
        $jiraWorklogEntryTransfer->comment = 'comment one';
        $jiraWorklogEntryTransfer->timeSpentSeconds = 3600;

        $jiraWorklogEntryTransfer2 = new JiraWorklogEntryTransfer();
        $jiraWorklogEntryTransfer2->key = 'Ticket two';
        $jiraWorklogEntryTransfer2->comment = 'comment two';
        $jiraWorklogEntryTransfer2->timeSpentSeconds = 900;

        $jiraWorklogsTransfer->jiraWorklogEntryCollection = new JiraWorklogEntryCollectionTransfer(
            [
                $jiraWorklogEntryTransfer,
                $jiraWorklogEntryTransfer2,
            ]
        );
        $issueReaderMock->expects(self::once())
            ->method('getCompleteWorklog')
            ->willReturn($jiraWorklogsTransfer);

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('success')
            ->with(
                "Daily Bookings: 01:00\nTicket one - comment one (01:00)\nTicket two - comment two (00:15)"
            );

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);

        $listBookingsCommand = new ListBookingsCommand(
            jiraIssueReader: $issueReaderMock,
            workflowFactory: $workflowFactoryMock
        );

        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        self::assertEquals(0, $listBookingsCommand->run($inputMock, $outputMock));
    }
}
