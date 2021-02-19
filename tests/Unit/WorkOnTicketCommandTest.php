<?php

namespace Unit\Workflow;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Console\WorkOnTicketCommand;
use Turbine\Workflow\Workflow\Model\WorkOnTicket;
use Turbine\Workflow\Workflow\Provider\BranchNameProvider;
use Turbine\Workflow\Workflow\Validator\BranchNameValidator;
use Turbine\Workflow\Workflow\WorkflowFactory;

class WorkOnTicketCommandTest extends TestCase
{
    public function testWorkOnTicket(): void
    {
        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('ask')
            ->with('Ticket number?')
            ->willReturn('ABC-134');

        $symfonyStyleMock->expects(self::once())
            ->method('askQuestion')
            ->willReturn('ABC-134-branch-name');

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->with($inputMock, $outputMock)
            ->willReturn($symfonyStyleMock);

        $branchNameValidatorMock = $this->createMock(BranchNameValidator::class);

        $workOnTicketMock = $this->createMock(WorkOnTicket::class);

        $branchNameProviderMock = $this->createMock(BranchNameProvider::class);
        $branchNameProviderMock->expects(self::once())
            ->method('getBranchNameFromTicket')
            ->with('ABC-134')
            ->willReturn('ABC-134-branch-name');

        $workOnTicketCommand = new WorkOnTicketCommand(
            $workflowFactoryMock,
            $branchNameValidatorMock,
            $workOnTicketMock,
            $branchNameProviderMock
        );

        self::assertEquals(0, $workOnTicketCommand->run($inputMock, $outputMock));
    }
}
