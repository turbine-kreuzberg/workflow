<?php

declare(strict_types=1);

namespace Turbine\Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Turbine\Workflow\Workflow\Model\MergeRequestCreator;
use Turbine\Workflow\Workflow\WorkflowFactory;

class CreateMergeRequestCommand extends Command
{
    public const COMMAND_NAME = 'merge-request:create';

    public function __construct(
        private MergeRequestCreator $mergeRequestCreator,
        private WorkflowFactory $workflowFactory
    ) {
        parent::__construct(self::COMMAND_NAME);
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Create merge request for current branch to develop.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mergeRequestUrl = $this->mergeRequestCreator->createForCurrentBranch();

        $inputOutputStyle = $this->workflowFactory->createSymfonyStyle($input, $output);
        $inputOutputStyle->success(
            [
                'Created merge request: ' . $mergeRequestUrl,
                'The ticket was moved to code review.',
            ]
        );

        return 0;
    }
}
