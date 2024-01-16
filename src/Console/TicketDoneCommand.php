<?php

declare(strict_types=1);

namespace Turbine\Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Turbine\Workflow\Client\GitClient;
use Turbine\Workflow\Client\GitlabClient;
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
        private GitlabClient $gitlabClient,
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
        $jiraDevelopmentDoneStatus = $this->configuration->get(Configuration::JIRA_DEVELOPMENT_DONE_STATUS);
        $this->issueUpdater->moveIssueToStatus(
            $ticketId,
            $jiraDevelopmentDoneStatus
        );
        $this->gitlabClient->deleteRemoteBranch($currentBranchName);

        $inputOutputStyle->success(
            "Remote '{$currentBranchName}' was deleted and ticket was moved to '{$jiraDevelopmentDoneStatus}'!"
        );

        return 0;
    }

    private function isInvalidBranch(string $currentBranchName): bool
    {
        $protectedBranches = [
            $this->configuration->get(Configuration::BRANCH_DEPLOYMENT),
            $this->configuration->get(Configuration::BRANCH_DEVELOPMENT),
        ];

        return in_array($currentBranchName, $protectedBranches, true);
    }
}
