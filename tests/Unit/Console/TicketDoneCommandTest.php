<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Client\GitClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Console\TicketDoneCommand;
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
            $workflowFactoryMock
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
            $workflowFactoryMock
        );

        self::assertEquals(1, $workOnTicketCommand->run($inputMock, $outputMock));
    }

    public function testCommandConfiguration(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $gitClientMock = $this->createMock(GitClient::class);
        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);


        $workOnTicketCommand = new TicketDoneCommand(
            $configurationMock,
            $gitClientMock,
            $workflowFactoryMock
        );

        self::assertEquals('workflow:ticket-done', $workOnTicketCommand->getName());
        self::assertEquals(
            'Moves ticket to merged to develop status and deletes the branch',
            $workOnTicketCommand->getDescription()
        );
    }
}
