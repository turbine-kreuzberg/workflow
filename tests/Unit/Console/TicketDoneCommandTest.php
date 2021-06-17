<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Client\GitClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Console\TicketDoneCommand;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\TicketIdentifier;
use Turbine\Workflow\Workflow\WorkflowFactory;

class TicketDoneCommandTest extends TestCase
{
    public function testCommandShowsErrorCodeIfCurrentBranchIsTheDevelopmentBranch(): void
    {
        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('error')
            ->with('This command works only on feature branches!');

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->with($inputMock, $outputMock)
            ->willReturn($symfonyStyleMock);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['BRANCH_DEPLOYMENT'], ['BRANCH_DEVELOPMENT'])
            ->willReturn('develop', 'main');

        $gitClientMock = $this->createMock(GitClient::class);
        $gitClientMock->expects(self::once())
            ->method('getCurrentBranchName')
            ->willReturn('develop');

        $workOnTicketCommand = new TicketDoneCommand(
            $configurationMock,
            $gitClientMock,
            $workflowFactoryMock,
            $this->createMock(TicketIdentifier::class),
            $this->createMock(IssueUpdater::class)
        );

        self::assertEquals(1, $workOnTicketCommand->run($inputMock, $outputMock));
    }

    public function testCommandShowsErrorCodeIfCurrentBranchIsTheDeploymentBranch(): void
    {
        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('error')
            ->with('This command works only on feature branches!');

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->with($inputMock, $outputMock)
            ->willReturn($symfonyStyleMock);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['BRANCH_DEPLOYMENT'], ['BRANCH_DEVELOPMENT'])
            ->willReturn('develop', 'main');


        $gitClientMock = $this->createMock(GitClient::class);
        $gitClientMock->expects(self::once())
            ->method('getCurrentBranchName')
            ->willReturn('main');

        $workOnTicketCommand = new TicketDoneCommand(
            $configurationMock,
            $gitClientMock,
            $workflowFactoryMock,
            $this->createMock(TicketIdentifier::class),
            $this->createMock(IssueUpdater::class)
        );

        self::assertEquals(1, $workOnTicketCommand->run($inputMock, $outputMock));
    }
    
    public function testCommandDeletesBranchAndMovesTicket(): void
    {
        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->with($inputMock, $outputMock)
            ->willReturn($symfonyStyleMock);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(['BRANCH_DEPLOYMENT'], ['BRANCH_DEVELOPMENT'], ['JIRA_DEVELOPMENT_DONE_STATUS'])
            ->willReturn('develop', 'main', 'merged to develop');

        $gitClientMock = $this->createMock(GitClient::class);
        $gitClientMock->expects(self::once())
            ->method('getCurrentBranchName')
            ->willReturn('abc-123-branchToDelete');

        $gitClientMock->expects(self::once())
            ->method('deleteRemoteBranch')
            ->with('abc-123-branchToDelete');

        $ticketIdentifierMock = $this->createMock(TicketIdentifier::class);
        $ticketIdentifierMock->expects(self::once())
            ->method('extractFromBranchName')
            ->with('abc-123-branchToDelete')
            ->willReturn('abc-123');

        $issueUpdaterMock = $this->createMock(IssueUpdater::class);
        $issueUpdaterMock->expects(self::once())
            ->method('moveIssueToStatus')
            ->with('abc-123', 'merged to develop');

        $workOnTicketCommand = new TicketDoneCommand(
            $configurationMock,
            $gitClientMock,
            $workflowFactoryMock,
            $ticketIdentifierMock,
            $issueUpdaterMock
        );

        self::assertEquals(0, $workOnTicketCommand->run($inputMock, $outputMock));
    }

    public function testCommandConfiguration(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $gitClientMock = $this->createMock(GitClient::class);
        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);


        $workOnTicketCommand = new TicketDoneCommand(
            $configurationMock,
            $gitClientMock,
            $workflowFactoryMock,
            $this->createMock(TicketIdentifier::class),
            $this->createMock(IssueUpdater::class)
        );

        self::assertEquals('workflow:ticket-done', $workOnTicketCommand->getName());
        self::assertEquals(
            'Moves ticket to JIRA_DEVELOPMENT_DONE_STATUS and deletes the branch',
            $workOnTicketCommand->getDescription()
        );
    }
}
