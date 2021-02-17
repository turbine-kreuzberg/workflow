<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Console\CreateMergeRequestCommand;
use Turbine\Workflow\Workflow\Model\MergeRequestCreator;
use Turbine\Workflow\Workflow\WorkflowFactory;

class CreateMergeRequestCommandTest extends TestCase
{
    public function testCreateMergeRequest(): void
    {
        $mergeRequestCreatorMock = $this->createMock(MergeRequestCreator::class);
        $mergeRequestCreatorMock->expects(self::once())
            ->method('createForCurrentBranch')
            ->willReturn('url');

        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('success')
            ->with(
                [
                    'Created merge request: url',
                    'The ticket was moved to code review.'
                ]
            );

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->with($inputMock, $outputMock)
            ->willReturn($symfonyStyleMock);

        $createMergeRequestCommand = new CreateMergeRequestCommand(
            $mergeRequestCreatorMock,
            $workflowFactoryMock
        );

        $createMergeRequestCommand->run($inputMock, $outputMock);
    }
}
