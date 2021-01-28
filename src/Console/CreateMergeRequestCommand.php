<?php

namespace Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Workflow\Workflow\Model\MergeRequestCreator;

class CreateMergeRequestCommand extends Command
{
    public const COMMAND_NAME = 'workflow:create-merge-request';

    public function __construct(
        private MergeRequestCreator $mergeRequestCreator,
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

        $inputOutputStyle = new SymfonyStyle($input, $output);
        $inputOutputStyle->success(
            [
                'Created merge request: ' . $mergeRequestUrl,
                'The ticket was moved to code review.',
            ]
        );

        return 0;
    }
}
