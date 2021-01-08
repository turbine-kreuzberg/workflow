<?php

namespace Workflow\Workflow\Model;

use Workflow\Client\GitClient;
use Workflow\Client\JiraClient;
use Workflow\Configuration;
use Workflow\Transfers\JiraIssueTransfer;

class WorkOnTicket
{
    private const TRANSITION_ID_IN_PROGRESS = '691';

    public function __construct(
        private JiraClient $jiraClient,
        private GitClient $gitClient,
        private Configuration $configuration
    ) {

    }

    public function workOnTicket(string $ticketNumber, string $branchName): void
    {
        $issueKey = $this->configuration->getConfiguration(Configuration::JIRA_PROJECT_KEY) . '-' . $ticketNumber;
        $this->gitClient->createBranchOnTopOf(
            $this->configuration->getConfiguration(Configuration::BRANCH_DEVELOPMENT),
            $branchName
        );
        $this->jiraClient->assignJiraIssueToUser($issueKey);
        $this->jiraClient->transitionJiraIssue($issueKey, self::TRANSITION_ID_IN_PROGRESS);
    }

    public function getBranchNameFromTicket(string $ticketNumber): string
    {
        $jiraIssueTransfer = $this->getJiraIssue($ticketNumber);

        $description = preg_replace(
            '/[^a-z0-9-]/',
            '-',
            strtolower($jiraIssueTransfer->summary)
        );

        $branchName = sprintf('%s-%s', $jiraIssueTransfer->key, $description);

        return $branchName;
    }

    private function getJiraIssue(string $ticketNumber): JiraIssueTransfer
    {
        $issueKey = $this->configuration->getConfiguration(Configuration::JIRA_PROJECT_KEY) . '-' . $ticketNumber;
        $jiraIssueTransfer = $this->jiraClient->getIssue($issueKey);

        return $jiraIssueTransfer;
    }
}
