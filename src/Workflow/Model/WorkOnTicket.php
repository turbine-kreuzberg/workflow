<?php

namespace Turbine\Workflow\Workflow\Model;

use Turbine\Workflow\Client\GitClient;
use Turbine\Workflow\Client\JiraClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;

class WorkOnTicket
{
    private const STATUS_IN_PROGRESS = 'in progress';

    public function __construct(
        private JiraClient $jiraClient,
        private GitClient $gitClient,
        private Configuration $configuration,
        private IssueUpdater $issueUpdater
    ) {

    }

    public function workOnTicket(string $ticketNumber, string $branchName): void
    {
        $issueKey = $this->configuration->get(Configuration::JIRA_PROJECT_KEY) . '-' . $ticketNumber;

        $this->gitClient->createBranchOnTopOf(
            $this->configuration->get(Configuration::BRANCH_DEVELOPMENT),
            $branchName
        );
        $this->jiraClient->assignJiraIssueToUser($issueKey);
        $this->issueUpdater->moveIssueToStatus($issueKey, self::STATUS_IN_PROGRESS);
    }
}
