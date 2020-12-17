<?php

namespace Workflow\Workflow\Jira;

use Workflow\Client\JiraClient;
use Workflow\Configuration;
use Workflow\Transfers\JiraIssueTransfer;

class IssueCreator
{
    private const ACCOUNT_CUSTOM_FIELD = 'customfield_10002';
    private const ACCOUNT_ID = '162';
    private const SPRINT_FIELD = 'customfield_10007';

    private const TRANSITION_ID_DEVELOPER_APPROVAL = '821';
    private const TRANSITION_ID_CODE_BACKLOG = '711';

    public function __construct(private JiraClient $jiraClient, private Configuration $configuration)
    {
    }

    public function createIssue(string $summary, string $issueType): JiraIssueTransfer
    {
        $issueData = $this->createIssueData($summary, $issueType);

        return $this->jiraClient->createIssue($issueData);
    }

    public function createIssueForSprint(string $summary, string $issueType): JiraIssueTransfer
    {
        $activeSprint = $this->jiraClient->getActiveSprint();

        $issueData = $this->createIssueData($summary, $issueType);
        $issueData['fields'][self::SPRINT_FIELD] = $activeSprint['id'];

        $jiraIssueTransfer = $this->jiraClient->createIssue($issueData);
        $this->jiraClient->transitionJiraIssue($jiraIssueTransfer->key, self::TRANSITION_ID_DEVELOPER_APPROVAL);
        $this->jiraClient->transitionJiraIssue($jiraIssueTransfer->key, self::TRANSITION_ID_CODE_BACKLOG);

        return $jiraIssueTransfer;
    }

    private function createDescription(string $summary, string $issueType): string
    {
        if ($issueType === 'bug') {
            return 'h1.Symptom
'
                . $summary .
                '

h1.How to reproduce

h1.Expected Behavior

h1.Details
';
        }

        return 'h1.Details
'
            . $summary .
            '
';
    }

    private function createIssueData(string $summary, string $issueType): array
    {
        $issueData = [
            'fields' => [
                'summary' => $summary,
                'description' => $this->createDescription($summary, $issueType),
                'issuetype' => [
                    'name' => ucfirst($issueType),
                ],
                'labels' => [ucfirst($issueType)],
                'components' => [
                    [
                        'name' => ucfirst($issueType) . 's',
                    ],
                ],
            ],
        ];

        return array_merge_recursive($issueData, $this->getDefaultIssueData());
    }

    private function getDefaultIssueData(): array
    {
        return [
            'fields' => [
                'project' => [
                    'key' => $this->configuration->getConfiguration(Configuration::JIRA_PROJECT_KEY),
                ],
                self::ACCOUNT_CUSTOM_FIELD => self::ACCOUNT_ID,
            ],
        ];
    }
}
