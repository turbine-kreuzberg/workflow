<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
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

        $branchName = "ABC-134-branch-name-long-long-long-long-long-too-l";
        $question = new Question(
            "Branch name?\n   {$branchName}\n   --------------------------------------------------",
            $branchName
        );
        $question->setAutocompleterValues(['ABC-134-branch-name']);
        $question->setValidator(
            function (string $name): string {
                return $name;
            }
        );

        $symfonyStyleMock->expects(self::once())
            ->method('askQuestion')
            ->with($question)
            ->willReturn('ABC-134-branch-name');

        $symfonyStyleMock->expects(self::once())
            ->method('success')
            ->with(
                [
                    'Created new branch: ABC-134-branch-name',
                    'Moved ticket to in progress.'
                ]
            );

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->with($inputMock, $outputMock)
            ->willReturn($symfonyStyleMock);

        $branchNameValidatorMock = $this->createMock(BranchNameValidator::class);

        $workOnTicketMock = $this->createMock(WorkOnTicket::class);
        $workOnTicketMock->expects(self::once())
            ->method('workOnTicket')
            ->with('ABC-134', 'ABC-134-branch-name');

        $branchNameProviderMock = $this->createMock(BranchNameProvider::class);
        $branchNameProviderMock->expects(self::once())
            ->method('getBranchNameFromTicket')
            ->with('ABC-134')
            ->willReturn('ABC-134-branch-name-long-long-long-long-long-too-long');

        $workOnTicketCommand = new WorkOnTicketCommand(
            $workflowFactoryMock,
            $branchNameValidatorMock,
            $workOnTicketMock,
            $branchNameProviderMock
        );

        self::assertEquals(0, $workOnTicketCommand->run($inputMock, $outputMock));
    }

    public function testCommandConfiguration(): void
    {
        $workOnTicketCommand = new WorkOnTicketCommand(
            $this->createMock(WorkflowFactory::class),
            $this->createMock(BranchNameValidator::class),
            $this->createMock(WorkOnTicket::class),
            $this->createMock(BranchNameProvider::class)
        );

        self::assertEquals('workflow:work-on-ticket', $workOnTicketCommand->getName());
        self::assertEquals(
            'creates a new feature branch for the given ticket and assigns the task to the developer',
            $workOnTicketCommand->getDescription()
        );
    }
}
