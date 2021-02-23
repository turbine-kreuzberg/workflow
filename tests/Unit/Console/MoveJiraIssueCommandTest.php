<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Console\MoveJiraIssueCommand;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\Provider\TicketTransitionStatusChoicesProvider;
use Turbine\Workflow\Workflow\WorkflowFactory;

class MoveJiraIssueCommandTest extends TestCase
{
    public function testMoveTicketAndAssignToUserWithoutArgument(): void
    {
        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock
            ->expects(self::once())
            ->method('ask')
            ->with('Ticket number')
            ->willReturn('12345');
        $symfonyStyleMock
            ->expects(self::once())
            ->method('choice')
            ->willReturn('Choice 2');
        $symfonyStyleMock
            ->expects(self::once())
            ->method('confirm')
            ->with('Assign yourself to the ticket?')
            ->willReturn(true);
        $symfonyStyleMock
            ->expects(self::exactly(2))
            ->method('success')
            ->withConsecutive(['Ticket moved successfully to status Choice 2'], ['Bye!!']);

        $issueUpdaterMock = $this->createMock(IssueUpdater::class);
        $issueUpdaterMock
            ->expects(self::once())
            ->method('moveIssueToStatus')
            ->with('12345', 'Choice 2');
        $issueUpdaterMock
            ->expects(self::once())
            ->method('assignJiraIssueToUser')
            ->with('12345');

        $ticketTransitionsStatusProviderMock = $this->createMock(TicketTransitionStatusChoicesProvider::class);
        $ticketTransitionsStatusProviderMock
            ->expects(self::once())
            ->method('provide')
            ->willReturn(['Choice 1', 'Choice 2']);

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);
        $workflowFactoryMock->expects(self::once())
            ->method('createTicketTransitionStatusChoicesProvider')
            ->willReturn($ticketTransitionsStatusProviderMock);

        $moveJiraIssueCommand = new MoveJiraIssueCommand(
            workflowFactory: $workflowFactoryMock,
            issueUpdater: $issueUpdaterMock
        );

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock
            ->expects(self::once())
            ->method('getArgument')
            ->with('ticket number')
            ->willReturn(null);
        $outputMock = $this->createMock(OutputInterface::class);

        $moveJiraIssueCommand->run($inputMock, $outputMock);
    }

    public function testMoveTicketAndNotAssignToUserWithoutArgument(): void
    {
        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock
            ->expects(self::once())
            ->method('ask')
            ->with('Ticket number')
            ->willReturn('12345');
        $symfonyStyleMock
            ->expects(self::once())
            ->method('choice')
            ->willReturn('Choice 2');
        $symfonyStyleMock
            ->expects(self::once())
            ->method('confirm')
            ->with('Assign yourself to the ticket?')
            ->willReturn(false);
        $symfonyStyleMock
            ->expects(self::exactly(2))
            ->method('success')
            ->withConsecutive(['Ticket moved successfully to status Choice 2'], ['Bye!!']);

        $issueUpdaterMock = $this->createMock(IssueUpdater::class);
        $issueUpdaterMock
            ->expects(self::once())
            ->method('moveIssueToStatus')
            ->with('12345', 'Choice 2');
        $issueUpdaterMock
            ->expects(self::never())
            ->method('assignJiraIssueToUser');

        $ticketTransitionsStatusProviderMock = $this->createMock(TicketTransitionStatusChoicesProvider::class);
        $ticketTransitionsStatusProviderMock
            ->expects(self::once())
            ->method('provide')
            ->willReturn(['Choice 1', 'Choice 2']);

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);
        $workflowFactoryMock->expects(self::once())
            ->method('createTicketTransitionStatusChoicesProvider')
            ->willReturn($ticketTransitionsStatusProviderMock);

        $moveJiraIssueCommand = new MoveJiraIssueCommand(
            workflowFactory: $workflowFactoryMock,
            issueUpdater: $issueUpdaterMock
        );

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock
            ->expects(self::once())
            ->method('getArgument')
            ->with('ticket number')
            ->willReturn(null);
        $outputMock = $this->createMock(OutputInterface::class);

        $moveJiraIssueCommand->run($inputMock, $outputMock);
    }

    public function testMoveTicketAndNotAssignToUserWithArgument(): void
    {
        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock
            ->expects(self::never())
            ->method('ask');
        $symfonyStyleMock
            ->expects(self::once())
            ->method('choice')
            ->willReturn('Choice 2');
        $symfonyStyleMock
            ->expects(self::once())
            ->method('confirm')
            ->with('Assign yourself to the ticket?')
            ->willReturn(false);
        $symfonyStyleMock
            ->expects(self::exactly(2))
            ->method('success')
            ->withConsecutive(['Ticket moved successfully to status Choice 2'], ['Bye!!']);

        $issueUpdaterMock = $this->createMock(IssueUpdater::class);
        $issueUpdaterMock
            ->expects(self::once())
            ->method('moveIssueToStatus')
            ->with('6789', 'Choice 2');
        $issueUpdaterMock
            ->expects(self::never())
            ->method('assignJiraIssueToUser');

        $ticketTransitionsStatusProviderMock = $this->createMock(TicketTransitionStatusChoicesProvider::class);
        $ticketTransitionsStatusProviderMock
            ->expects(self::once())
            ->method('provide')
            ->willReturn(['Choice 1', 'Choice 2']);

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);
        $workflowFactoryMock->expects(self::once())
            ->method('createTicketTransitionStatusChoicesProvider')
            ->willReturn($ticketTransitionsStatusProviderMock);

        $moveJiraIssueCommand = new MoveJiraIssueCommand(
            workflowFactory: $workflowFactoryMock,
            issueUpdater: $issueUpdaterMock
        );

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock
            ->expects(self::once())
            ->method('getArgument')
            ->with('ticket number')
            ->willReturn('6789');
        $outputMock = $this->createMock(OutputInterface::class);

        $moveJiraIssueCommand->run($inputMock, $outputMock);
    }

    public function testMoveTicketWhenNoTransitionsAvailableShouldNotDoAnything(): void
    {
        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock
            ->expects(self::once())
            ->method('ask')
            ->with('Ticket number')
            ->willReturn('112233');
        $symfonyStyleMock
            ->expects(self::never())
            ->method('choice');
        $symfonyStyleMock
            ->expects(self::never())
            ->method('confirm');
        $symfonyStyleMock
            ->expects(self::never())
            ->method('success');
        $symfonyStyleMock
            ->expects(self::once())
            ->method('writeLn')
            ->with('There are no transitions possible for this ticket.');

        $issueUpdaterMock = $this->createMock(IssueUpdater::class);
        $issueUpdaterMock
            ->expects(self::never())
            ->method('moveIssueToStatus');
        $issueUpdaterMock
            ->expects(self::never())
            ->method('assignJiraIssueToUser');

        $ticketTransitionsStatusProviderMock = $this->createMock(TicketTransitionStatusChoicesProvider::class);
        $ticketTransitionsStatusProviderMock
            ->expects(self::once())
            ->method('provide')
            ->willReturn([]);

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);
        $workflowFactoryMock->expects(self::once())
            ->method('createTicketTransitionStatusChoicesProvider')
            ->willReturn($ticketTransitionsStatusProviderMock);

        $moveJiraIssueCommand = new MoveJiraIssueCommand(
            workflowFactory: $workflowFactoryMock,
            issueUpdater: $issueUpdaterMock
        );

        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $moveJiraIssueCommand->run($inputMock, $outputMock);
    }
}
