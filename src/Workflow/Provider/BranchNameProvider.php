<?php

declare(strict_types=1);

namespace Turbine\Workflow\Workflow\Provider;

use Turbine\Workflow\Client\GitClient;
use Turbine\Workflow\Client\JiraClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Transfers\JiraIssueTransfer;

class BranchNameProvider
{
    public function __construct(
        private JiraClient $jiraClient,
        private GitClient $gitClient,
        private Configuration $configuration,
    ) {
    }

    public function getCurrentBranchName(): string
    {
        return $this->gitClient->getCurrentBranchName();
    }

    public function getBranchNameFromTicket(string $ticketNumber): string
    {
        $jiraIssueTransfer = $this->getJiraIssue($ticketNumber);

        $description = preg_replace(
            '/[^a-z0-9-]/',
            '-',
            strtolower($jiraIssueTransfer->summary)
        );

        return sprintf('%s-%s', $jiraIssueTransfer->key, $description);
    }

    private function getJiraIssue(string $ticketNumber): JiraIssueTransfer
    {
        $issueKey = $this->configuration->get(Configuration::JIRA_PROJECT_KEY) . '-' . $ticketNumber;

        return $this->jiraClient->getIssue($issueKey);
    }
}
