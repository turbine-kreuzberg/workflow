<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Console\BookTimeCommand;
use Turbine\Workflow\Console\SubConsole\FastBookTimeConsole;
use Turbine\Workflow\Console\SubConsole\TicketNumberConsole;
use Turbine\Workflow\Transfers\JiraWorklogEntryTransfer;
use Turbine\Workflow\Workflow\Jira\IssueReader;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\Provider\WorklogChoicesProvider;
use Turbine\Workflow\Workflow\TicketIdProvider;
use Turbine\Workflow\Workflow\WorkflowFactory;

class BookTimeCommandTest extends TestCase
{
    public function testFastBookTime(): void
    {
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->expects(self::once())
            ->method('getOption')
            ->with('fast-worklog')
            ->willReturn(true);

        $outputMock = $this->createMock(OutputInterface::class);

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->with($inputMock, $outputMock)
            ->willReturn($symfonyStyleMock);

        $configurationMock = $this->createMock(Configuration::class);

        $fastBookTimeConsoleMock = $this->createMock(FastBookTimeConsole::class);
        $fastBookTimeConsoleMock->expects(self::once())
            ->method('execFastBooking')
            ->with($symfonyStyleMock, date('Y-m-d') . 'T12:00:00.000+0000')
            ->willReturn(true);

        $issueUpdaterMock = $this->createMock(IssueUpdater::class);
        $issueReaderMock = $this->createMock(IssueReader::class);
        $ticketIdProviderMock = $this->createMock(TicketIdProvider::class);
        $worklogChoicesProviderMock = $this->createMock(WorklogChoicesProvider::class);

        $ticketNumberConsoleMock = $this->createMock(TicketNumberConsole::class);

        $bookTimeCommand = new BookTimeCommand(
            $configurationMock,
            $workflowFactoryMock,
            $issueUpdaterMock,
            $issueReaderMock,
            $fastBookTimeConsoleMock,
            $ticketIdProviderMock,
            $worklogChoicesProviderMock,
            $ticketNumberConsoleMock
        );

        $bookTimeCommand->run($inputMock, $outputMock);
    }

    public function testBookTime(): void
    {
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->expects(self::exactly(2))
            ->method('getOption')
            ->withConsecutive(['fast-worklog'], ['forCurrentBranch'])
            ->willReturnOnConsecutiveCalls(false, true);

        $outputMock = $this->createMock(OutputInterface::class);

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('choice')
            ->with('Choose your worklog comment')
            ->willReturn('message');

        $symfonyStyleMock->expects(self::once())
            ->method('ask')
            ->with('For how long did you do it')
            ->willReturn(100.0);

        $symfonyStyleMock->expects(self::once())
            ->method('success')
            ->with("Booked 3600 minutes for \"message\" on ABC-134\nTotal booked time today: 8.5h");

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->with($inputMock, $outputMock)
            ->willReturn($symfonyStyleMock);

        $configurationMock = $this->createMock(Configuration::class);

        $fastBookTimeConsoleMock = $this->createMock(FastBookTimeConsole::class);

        $jiraWorklogEntry = new JiraWorklogEntryTransfer();
        $jiraWorklogEntry->timeSpentSeconds = 100;
        $jiraWorklogEntry->key = 'ABC-134';

        $issueUpdaterMock = $this->createMock(IssueUpdater::class);
        $issueUpdaterMock->expects(self::once())
            ->method('bookTime')
            ->with('ABC-134', 'message', 100,  date('Y-m-d') . 'T12:00:00.000+0000')
            ->willReturn(3600.0);


        $issueReaderMock = $this->createMock(IssueReader::class);
        $issueReaderMock->expects(self::once())
            ->method('getLastTicketWorklog')
            ->with('ABC-134')
            ->willReturn($jiraWorklogEntry);

        $issueReaderMock->expects(self::once())
            ->method('getTimeSpentToday')
            ->willReturn(8.5);

        $ticketIdProviderMock = $this->createMock(TicketIdProvider::class);
        $ticketIdProviderMock->expects(self::once())
            ->method('extractTicketIdFromCurrentBranch')
            ->willReturn('ABC-134');

        $worklogChoicesProviderMock = $this->createMock(WorklogChoicesProvider::class);
        $worklogChoicesProviderMock->expects(self::once())
            ->method('provide')
            ->with('ABC-134')
            ->willReturn([]);

        $ticketNumberConsoleMock = $this->createMock(TicketNumberConsole::class);

        $bookTimeCommand = new BookTimeCommand(
            $configurationMock,
            $workflowFactoryMock,
            $issueUpdaterMock,
            $issueReaderMock,
            $fastBookTimeConsoleMock,
            $ticketIdProviderMock,
            $worklogChoicesProviderMock,
            $ticketNumberConsoleMock
        );

        $bookTimeCommand->run($inputMock, $outputMock);
    }
}
