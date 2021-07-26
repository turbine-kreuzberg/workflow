<?php

declare(strict_types=1);

namespace Turbine\Workflow\Workflow\Model;

use Turbine\Workflow\Client\GitClient;
use Turbine\Workflow\Client\GitlabClient;
use Turbine\Workflow\Client\JiraClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\TicketIdProvider;

class MergeRequestCreator
{
    private const STATUS_CODE_REVIEW = 'code review';

    public function __construct(
        private GitlabClient $gitlabClient,
        private JiraClient $jiraClient,
        private GitClient $gitClient,
        private Configuration $configuration,
        private TicketIdProvider $ticketIdProvider,
        private IssueUpdater $issueUpdater,
    ) {
    }

    public function createForCurrentBranch(): string
    {
        $currentBranchName = $this->gitClient->getCurrentBranchName();

        $currentIssue = $this->ticketIdProvider->extractTicketIdFromCurrentBranch();
        $jiraIssueTransfer = $this->jiraClient->getIssue($currentIssue);

        $description = $this->createDescription($currentIssue);

        $mergeRequestData = [
            'source_branch' => $currentBranchName,
            'target_branch' => $this->configuration->get(Configuration::BRANCH_DEVELOPMENT),
            'title' => '[' . $jiraIssueTransfer->key . '] ' . $jiraIssueTransfer->summary,
            'description' => $description,
            'remove_source_branch' => true,
            'labels' => implode(',', $jiraIssueTransfer->labels),
            'approvals_before_merge' => 2,
        ];

        $mergeRequestUrl = $this->gitlabClient->createMergeRequest($mergeRequestData);
        $this->issueUpdater->moveIssueToStatus(
            $jiraIssueTransfer->key,
            self::STATUS_CODE_REVIEW
        );

        return $mergeRequestUrl;
    }

    private function createDescription(string $currentIssue): string
    {
        $gitLog = $this->gitClient->getGitLog();

        $description = $gitLog . PHP_EOL;
        $description .= 'Closes ' . $currentIssue . PHP_EOL;

        return $description;
    }
}
