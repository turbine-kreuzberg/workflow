<?php

namespace Workflow\Client;

use Exception;
use Workflow\Client\Http\AtlassianHttpClient;
use Workflow\Configuration;
use Workflow\Exception\JiraStateNotFoundException;
use Workflow\Transfers\JiraIssueTransfer;
use Workflow\Transfers\JiraIssueTransferCollection;
use Workflow\Workflow\Jira\Mapper\JiraIssueMapper;

class JiraClient
{
    private const BASE_URL = 'https://jira.votum.info:7443/';
    private const API_URL = self::BASE_URL . 'rest/api/latest/';
    private const SEARCH_URL = self::API_URL . 'search/';
    private const BROWSE_URL = self::BASE_URL . 'browse/';
    private const ISSUE_URL = self::API_URL . 'issue/';
    private const BOARD_URL = self::BASE_URL . 'rest/agile/1.0/board/';


    public function __construct(
        private AtlassianHttpClient $jiraHttpClient,
        private Configuration $configuration,
        private JiraIssueMapper $jiraIssueMapper
    ) {
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

    public function getIssueTransitions(string $issue): array
    {
        return $this->jiraHttpClient->get(static::ISSUE_URL . $issue . '/' . 'transitions');
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
        return $this->configuration->getConfiguration(Configuration::JIRA_USERNAME);
    }

    public function getUserLocale(): string
    {
        $localeCall = self::API_URL . 'myself';
        return $this->jiraHttpClient->get($localeCall)['locale'];
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
        $jiraIssueTransfer = $this->jiraIssueMapper->map($issue);
        $jiraIssueTransfer->url = self::BROWSE_URL . $jiraIssueTransfer->key;

        return $jiraIssueTransfer;
    }

    private function getProjectName(): string
    {
        return $this->configuration->getConfiguration(Configuration::PROJECT_NAME);
    }

    private function getBoardId(): string
    {
        return $this->configuration->getConfiguration(Configuration::BOARD_ID);
    }
}
