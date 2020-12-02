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

    private AtlassianHttpClient $jiraHttpClient;

    /**
     * @param \Workflow\Client\Http\AtlassianHttpClient $jiraHttpClient
     */
    public function __construct(AtlassianHttpClient $jiraHttpClient)
    {
        $this->jiraHttpClient = $jiraHttpClient;
    }

    /**
     * @return array
     */
    public static function requiredEnvironmentVariables(): array
    {
        return [AtlassianHttpClient::USERNAME, AtlassianHttpClient::PASSWORD, self::PROJECT_NAME];
    }

    /**
     * @param array $issueData
     *
     * @return \Workflow\Transfers\JiraIssueTransfer
     */
    public function createIssue(array $issueData): JiraIssueTransfer
    {
        $responseArray = $this->jiraHttpClient->post(self::ISSUE_URL, $issueData);

        return $this->getIssue($responseArray['key']);
    }

    /**
     * @param string $statusName
     *
     * @return \Workflow\Transfers\JiraIssueTransferCollection
     */
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

    /**
     * @throws \Exception
     *
     * @return array
     */
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

    /**
     * @param string $issue
     * @param string $transitionId
     *
     * @return void
     */
    public function transitionJiraIssue(string $issue, string $transitionId): void
    {
        $transitionData = ['transition' => ['id' => $transitionId]];
        $transitionUrl = self::ISSUE_URL . $issue . '/transitions';
        $this->jiraHttpClient->post($transitionUrl, $transitionData);
    }

    /**
     * @param string $issue
     *
     * @return void
     */
    public function assignJiraIssueToUser(string $issue): void
    {
        $assigneeData = ['name' => $this->getUsername()];
        $issueUrl = self::ISSUE_URL . $issue . '/assignee';
        $this->jiraHttpClient->put($issueUrl, $assigneeData);
    }

    /**
     * @throws \RuntimeException
     *
     * @return string
     */
    private function getUsername(): string
    {
        $userName = getenv(AtlassianHttpClient::USERNAME);
        if (empty($userName)) {
            throw new RuntimeException('No username provided. Please add it to your ".env" file.');
        }

        return $userName;
    }

    /**
     * @param string $issue
     * @param array $worklogEntry
     *
     * @return void
     */
    public function bookTime(string $issue, array $worklogEntry): void
    {
        $bookTimeCall = self::ISSUE_URL . $issue . '/worklog';
        $this->jiraHttpClient->post($bookTimeCall, $worklogEntry);
    }

    /**
     * @param string $issue
     *
     * @return array
     */
    public function getWorkLog(string $issue): array
    {
        $bookTimeCall = self::ISSUE_URL . $issue . '/worklog';

        return $this->jiraHttpClient->get($bookTimeCall);
    }

    /**
     * @param string $issue
     *
     * @return array
     */
    public function getIssue(string $issue): JiraIssueTransfer
    {
        $issueCall = self::ISSUE_URL . $issue;

        return $this->mapResponseToJiraIssueTransfer(
            $this->jiraHttpClient->get($issueCall)
        );
    }

    /**
     * @param array $responseArray
     *
     * @return \Workflow\Transfers\JiraIssueTransferCollection
     */
    private function mapResponseToJiraIssueTransferCollection(array $responseArray): JiraIssueTransferCollection
    {
        $jiraIssues = [];
        foreach ($responseArray['issues'] as $issue) {
            $jiraIssues[] = $this->mapResponseToJiraIssueTransfer($issue);
        }

        return new JiraIssueTransferCollection($jiraIssues);
    }

    /**
     * @param array $issue
     *
     * @return \Workflow\Transfers\JiraIssueTransfer
     */
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

    /**
     * @throws \Exception
     *
     * @return string
     */
    private function getProjectName(): string
    {
        $envVarname = self::PROJECT_NAME;
        if (getenv($envVarname)) {
            return getenv($envVarname);
        }

        throw new Exception('No project name provided. Please add to your ".env" file.');
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    private function getBoardId(): string
    {
        $envVarname = self::BOARD_ID;
        if (getenv($envVarname)) {
            return getenv($envVarname);
        }

        throw new Exception('No Jira board ID provided. Please add to your ".env" file.');
    }
}
