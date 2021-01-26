<?php

namespace Workflow\Model;

use Workflow\Client\GitClient;
use Workflow\Client\GitlabClient;
use Workflow\Client\JiraClient;
use Workflow\Configuration;
use Workflow\Workflow\Jira\IssueUpdater;
use Workflow\Workflow\TicketIdProvider;
use Workflow\Workflow\WorkflowFactory;

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
            'target_branch' => $this->configuration->getConfiguration(Configuration::BRANCH_DEVELOPMENT),
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

        $description = '';
        $description .= $gitLog . PHP_EOL;
        $description .= 'Closes ' . $currentIssue . PHP_EOL;

        return $description;
    }
}