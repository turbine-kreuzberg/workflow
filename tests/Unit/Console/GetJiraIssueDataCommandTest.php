<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Console\GetJiraIssueDataCommand;
use Turbine\Workflow\Console\ListBookingsCommand;
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
            ->with('Ticket number')
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

        $getJiraIssueDataCommand = new GetJiraIssueDataCommand(
            workflowFactory: $workflowFactoryMock,
            issueReader: $issueReaderMock
        );

        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $getJiraIssueDataCommand->run($inputMock, $outputMock);
    }

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

        $getJiraIssueDataCommand = new GetJiraIssueDataCommand(
            workflowFactory: $workflowFactoryMock,
            issueReader: $issueReaderMock
        );

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->expects(self::once())
            ->method('getArgument')
            ->with('ticket number')
            ->willReturn('12345');

        $outputMock = $this->createMock(OutputInterface::class);

        $getJiraIssueDataCommand->run($inputMock, $outputMock);
    }

    public function testGetTicketDataRepeatsTicketNumberQuestionIfTicketNotFound(): void
    {
        $testJiraIssueTransfer = $this->createDummyJiraIssueTransfer();

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::exactly(2))
            ->method('ask')
            ->with('Ticket number')
            ->willReturnOnConsecutiveCalls('99999', '12345');

        $issueReaderMock = $this->createMock(IssueReader::class);
        $issueReaderMock->expects(self::exactly(2))
            ->method('getIssue')
            ->willReturnOnConsecutiveCalls(self::throwException(new \Exception()), $testJiraIssueTransfer);

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);

        $getJiraIssueDataCommand = new GetJiraIssueDataCommand(
            workflowFactory: $workflowFactoryMock,
            issueReader: $issueReaderMock
        );

        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $getJiraIssueDataCommand->run($inputMock, $outputMock);
    }

    public function testGetTicketDataForTicketWithSubTasks(): void
    {
        $testJiraIssueTransfer = $this->createDummyJiraIssueTransfer();
        $testJiraIssueTransfer->subTasks = [
            [
                'key' => 'key',
                'fields' => [
                    'summary' => 'summary',
                    'status' => [
                        'name' => 'name',
                    ],
                ],
            ],
        ];

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::exactly(2))
            ->method('ask')
            ->with('Ticket number')
            ->willReturnOnConsecutiveCalls('99999', '12345');

        $issueReaderMock = $this->createMock(IssueReader::class);
        $issueReaderMock->expects(self::exactly(2))
            ->method('getIssue')
            ->willReturnOnConsecutiveCalls(self::throwException(new \Exception()), $testJiraIssueTransfer);

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);

        $getJiraIssueDataCommand = new GetJiraIssueDataCommand(
            workflowFactory: $workflowFactoryMock,
            issueReader: $issueReaderMock
        );

        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $getJiraIssueDataCommand->run($inputMock, $outputMock);
    }

    /**
     * @dataProvider parentTypeProvider
     *
     * @param string|null $parentType
     *
     * @return void
     */
    public function testGetTicketDataSubTaskWithDifferentTypes(?string $parentType): void
    {
        $testJiraIssueTransfer = $this->createDummyJiraIssueTransfer();
        $testJiraIssueTransfer->isSubTask = true;
        $testJiraIssueTransfer->parentIssueType = $parentType;
        $testJiraIssueTransfer->parentIssueKey = 'ABC-134';
        $testJiraIssueTransfer->parentIssueSummary = 'parent summary';

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

        $getJiraIssueDataCommand = new GetJiraIssueDataCommand(
            workflowFactory: $workflowFactoryMock,
            issueReader: $issueReaderMock
        );

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->expects(self::once())
            ->method('getArgument')
            ->with('ticket number')
            ->willReturn('12345');

        $outputMock = $this->createMock(OutputInterface::class);

        $getJiraIssueDataCommand->run($inputMock, $outputMock);
    }

    public function testCommandConfiguration(): void
    {
        $workOnTicketCommand = new GetJiraIssueDataCommand(
            $this->createMock(WorkflowFactory::class),
            $this->createMock(IssueReader::class)
        );

        self::assertEquals('workflow:get:jira-issue', $workOnTicketCommand->getName());
        self::assertEquals('Get data from a jira issue.', $workOnTicketCommand->getDescription());
    }

    public function parentTypeProvider(): array
    {
        return [
            'not known type' => ['randomtype'],
            'bug type' => ['bug'],
            'story type' => ['story'],
            'sub task type' => ['sub-task'],
            'no type' => [null],
        ];
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
