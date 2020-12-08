<?php

namespace Workflow\Client;

use Exception;
use RuntimeException;
use Workflow\Client\Http\AtlassianHttpClient;
use Workflow\Transfers\JiraIssueTransfer;
use Workflow\Transfers\JiraIssueTransferCollection;

class JiraClient
{
    private const PROJECT_NAME = 'JIRA_PROJECT_NAME';
    private const BOARD_ID = 'JIRA_BOARD_ID';

    private const BASE_URL = 'https://jira.votum.info:7443/';
    private const API_URL = self::BASE_URL . 'rest/api/latest/';
    private const SEARCH_URL = self::API_URL . 'search/';
    private const BROWSE_URL = self::BASE_URL . 'browse/';
    private const ISSUE_URL = self::API_URL . 'issue/';
    private const BOARD_URL = self::BASE_URL . 'rest/agile/1.0/board/';


    public function __construct(private AtlassianHttpClient $jiraHttpClient) {}

    public static function requiredEnvironmentVariables(): array
    {
        return [AtlassianHttpClient::USERNAME, AtlassianHttpClient::PASSWORD, self::PROJECT_NAME];
    }

    public function createIssue(array $issueData): JiraIssueTransfer
    {
        $responseArray = $this->jiraHttpClient->post(self::ISSUE_URL, $issueData);

        return $this->getIssue($responseArray['key']);
    }

    public function getCurrentIssuesInStatus(string $statusName): JiraIssueTransferCollection
    {
        $issueData = [
            'jql' => 'project = ' . $this->getProjectName() . ' AND status="' . $statusName . '"',
            'startAt' => 0,
            'fields' => ['id', 'key', 'summary', 'issuetype'],
        ];

        $responseArray = $this->jiraHttpClient->post(self::SEARCH_URL, $issueData);

        return $this->mapResponseToJiraIssueTransferCollection($responseArray);
    }

    public function getActiveSprint(): array
    {
        $responseArray = $this->jiraHttpClient->get(self::BOARD_URL . $this->getBoardId() . '/sprint');

        foreach ($responseArray['values'] as $sprint) {
            if ($sprint['state'] === 'active') {
                return $sprint;
            }
        }

        throw new Exception('No active sprint found.');
    }

    public function transitionJiraIssue(string $issue, string $transitionId): void
    {
        $transitionData = ['transition' => ['id' => $transitionId]];
        $transitionUrl = self::ISSUE_URL . $issue . '/transitions';
        $this->jiraHttpClient->post($transitionUrl, $transitionData);
    }

    public function assignJiraIssueToUser(string $issue): void
    {
        $assigneeData = ['name' => $this->getUsername()];
        $issueUrl = self::ISSUE_URL . $issue . '/assignee';
        $this->jiraHttpClient->put($issueUrl, $assigneeData);
    }

    private function getUsername(): string
    {
        $userName = getenv(AtlassianHttpClient::USERNAME);
        if (empty($userName)) {
            throw new RuntimeException('No username provided. Please add it to your ".env" file.');
        }

        return $userName;
    }

    public function bookTime(string $issue, array $worklogEntry): void
    {
        $bookTimeCall = self::ISSUE_URL . $issue . '/worklog';
        $this->jiraHttpClient->post($bookTimeCall, $worklogEntry);
    }

    public function getWorkLog(string $issue): array
    {
        $bookTimeCall = self::ISSUE_URL . $issue . '/worklog';

        return $this->jiraHttpClient->get($bookTimeCall);
    }

    public function getIssue(string $issue): JiraIssueTransfer
    {
        $issueCall = self::ISSUE_URL . $issue;

        return $this->mapResponseToJiraIssueTransfer(
            $this->jiraHttpClient->get($issueCall)
        );
    }

    private function mapResponseToJiraIssueTransferCollection(array $responseArray): JiraIssueTransferCollection
    {
        $jiraIssues = [];
        foreach ($responseArray['issues'] as $issue) {
            $jiraIssues[] = $this->mapResponseToJiraIssueTransfer($issue);
        }

        return new JiraIssueTransferCollection($jiraIssues);
    }

    private function mapResponseToJiraIssueTransfer(array $issue): JiraIssueTransfer
    {
        $jiraIssueTransfer = new JiraIssueTransfer();
        $jiraIssueTransfer->key = $issue['key'];
        $jiraIssueTransfer->summary = $issue['fields']['summary'];
        $jiraIssueTransfer->isSubTask = (bool)$issue['fields']['issuetype']['subtask'];
        $jiraIssueTransfer->labels = $issue['fields']['labels'] ?? [];
        $jiraIssueTransfer->url = self::BROWSE_URL . $issue['key'];

        return $jiraIssueTransfer;
    }

    private function getProjectName(): string
    {
        $envVarname = self::PROJECT_NAME;
        if (getenv($envVarname)) {
            return (string)getenv($envVarname);
        }

        throw new Exception('No project name provided. Please add to your ".env" file.');
    }

    private function getBoardId(): string
    {
        $envVarname = self::BOARD_ID;
        if (getenv($envVarname)) {
            return (string)getenv($envVarname);
        }

        throw new Exception('No Jira board ID provided. Please add to your ".env" file.');
    }
}
