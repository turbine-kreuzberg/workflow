<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Console\GetJiraIssueDataCommand;
use Turbine\Workflow\Transfers\JiraIssueTransfer;
use Turbine\Workflow\Workflow\Jira\IssueReader;
use Turbine\Workflow\Workflow\WorkflowFactory;

class GetJiraIssueDataCommandTest extends TestCase
{
    public function testGetTicketDataWithoutArgument(): void
    {
        $testJiraIssueTransfer = $this->createDummyJiraIssueTransfer();

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('ask')
            ->with('Ticket number (x to exit)')
            ->willReturn('12345');

        $issueReaderMock = $this->createMock(IssueReader::class);
        $issueReaderMock->expects(self::once())
            ->method('getIssue')
            ->with('12345')
            ->willReturn($testJiraIssueTransfer);

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);

        $workflowFactoryMock->expects(self::once())
            ->method('createJiraIssueReader')
            ->willReturn($issueReaderMock);

        $getJiraIssueDataCommand = new GetJiraIssueDataCommand(
            workflowFactory: $workflowFactoryMock,
        );

        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $getJiraIssueDataCommand->run($inputMock, $outputMock);
    }

    /**
     * @return void
     */
    public function testGetTicketDataWithArgument(): void
    {
        $testJiraIssueTransfer = $this->createDummyJiraIssueTransfer();

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::never())
            ->method('ask');

        $issueReaderMock = $this->createMock(IssueReader::class);
        $issueReaderMock->expects(self::once())
            ->method('getIssue')
            ->with('12345')
            ->willReturn($testJiraIssueTransfer);

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);

        $workflowFactoryMock->expects(self::once())
            ->method('createJiraIssueReader')
            ->willReturn($issueReaderMock);

        $getJiraIssueDataCommand = new GetJiraIssueDataCommand(
            workflowFactory: $workflowFactoryMock,
        );

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->expects(self::once())
            ->method('getArgument')
            ->with('ticket number')
            ->willReturn('12345');

        $outputMock = $this->createMock(OutputInterface::class);

        $getJiraIssueDataCommand->run($inputMock, $outputMock);
    }

    public function testGetTicketDataExitsWhenExitKeyPressed(): void
    {
        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('ask')
            ->with('Ticket number (x to exit)')
            ->willReturn('x');

        $issueReaderMock = $this->createMock(IssueReader::class);
        $issueReaderMock->expects(self::never())
            ->method('getIssue');

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);

        $workflowFactoryMock->expects(self::never())
            ->method('createJiraIssueReader');

        $getJiraIssueDataCommand = new GetJiraIssueDataCommand(
            workflowFactory: $workflowFactoryMock,
        );

        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $getJiraIssueDataCommand->run($inputMock, $outputMock);
    }

    public function testGetTicketDataRepeatsTicketNumberQuestionIfTicketNotFound(): void
    {
        $testJiraIssueTransfer = $this->createDummyJiraIssueTransfer();

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::exactly(2))
            ->method('ask')
            ->with('Ticket number (x to exit)')
            ->willReturnOnConsecutiveCalls('99999', '12345');

        $issueReaderMock = $this->createMock(IssueReader::class);
        $issueReaderMock->expects(self::exactly(2))
            ->method('getIssue')
            ->willReturnOnConsecutiveCalls(self::throwException(new \Exception()), $testJiraIssueTransfer);

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);

        $workflowFactoryMock->expects(self::exactly(2))
            ->method('createJiraIssueReader')
            ->willReturn($issueReaderMock);

        $getJiraIssueDataCommand = new GetJiraIssueDataCommand(
            workflowFactory: $workflowFactoryMock,
        );

        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $getJiraIssueDataCommand->run($inputMock, $outputMock);
    }

    private function createDummyJiraIssueTransfer(): JiraIssueTransfer
    {
        $testJiraIssueTransfer = new JiraIssueTransfer();

        $testJiraIssueTransfer->key = 'TEST-12345';
        $testJiraIssueTransfer->summary = 'summary';
        $testJiraIssueTransfer->type = 'task';
        $testJiraIssueTransfer->isSubTask = false;
        $testJiraIssueTransfer->description = 'test issue';
        $testJiraIssueTransfer->currentStatus = 'testing';
        $testJiraIssueTransfer->createdAt = 'today';
        $testJiraIssueTransfer->assignee = 'phpUnit';
        $testJiraIssueTransfer->timeSpent = 'eternity';
        $testJiraIssueTransfer->subTasks = [];
        $testJiraIssueTransfer->url = '';

        return $testJiraIssueTransfer;
    }
}
