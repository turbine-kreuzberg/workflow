<?php

namespace Turbine\Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Turbine\Workflow\Client\GitClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\TicketIdentifier;
use Turbine\Workflow\Workflow\WorkflowFactory;

class TicketDoneCommand extends Command
{
    public const COMMAND_NAME = 'workflow:ticket-done';

    public function __construct(
        private Configuration $configuration,
        private GitClient $gitClient,
        private WorkflowFactory $workflowFactory,
        private TicketIdentifier $ticketIdentifier,
        private IssueUpdater $issueUpdater
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('workflow:ticket-done');
        $this->setDescription(
            'Moves ticket to JIRA_DEVELOPMENT_DONE_STATUS and deletes the branch'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutputStyle = $this->workflowFactory->createSymfonyStyle($input, $output);
        $currentBranchName = $this->gitClient->getCurrentBranchName();

        if ($this->isInvalidBranch($currentBranchName)) {
            $inputOutputStyle->error('This command works only on feature branches!');

            return 1;
        }
        $ticketId = $this->ticketIdentifier->extractFromBranchName($currentBranchName);
        $this->issueUpdater->moveIssueToStatus(
            $ticketId,
            $this->configuration->get(Configuration::JIRA_DEVELOPMENT_DONE_STATUS)
        );
        $this->gitClient->deleteRemoteBranch($currentBranchName);

        return 0;
    }

    /**
     * @param string $currentBranchName
     *
     * @return bool
     */
    private function isInvalidBranch(string $currentBranchName): bool
    {
        $protectedBranches = [
            $this->configuration->get(Configuration::BRANCH_DEPLOYMENT),
            $this->configuration->get(Configuration::BRANCH_DEVELOPMENT)
        ];

        return in_array($currentBranchName, $protectedBranches, true);
    }
}
