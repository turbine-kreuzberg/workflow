<?php

namespace Turbine\Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Turbine\Workflow\Client\GitClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Workflow\WorkflowFactory;

class TicketDoneCommand extends Command
{
    public const COMMAND_NAME = 'workflow:ticket-done';

    public function __construct(
        private Configuration $configuration,
        private GitClient $gitClient,
        private WorkflowFactory $workflowFactory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('workflow:ticket-done');
        $this->setDescription(
            'Moves ticket to merged to develop status and deletes the branch'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutputStyle = $this->workflowFactory->createSymfonyStyle($input, $output);
        $currentBranchName = $this->gitClient->getCurrentBranchName();

        $protectedBranches = [
            $this->configuration->get(Configuration::BRANCH_DEPLOYMENT),
            $this->configuration->get(Configuration::BRANCH_DEVELOPMENT)
        ];

        if (in_array($currentBranchName, $protectedBranches, true)) {
            $inputOutputStyle->error('This command works only on feature branches!');

            return 1;
        }

        return 0;
    }
}
