<?php

namespace Turbine\Workflow\Client;

use Exception;
use Turbine\Workflow\Client\Http\AtlassianHttpClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Exception\JiraNoActiveSprintException;
use Turbine\Workflow\Transfers\JiraIssueTransfer;
use Turbine\Workflow\Workflow\Jira\Mapper\JiraIssueMapper;

class JiraClient
{
    private const BASE_URL = 'https://jira.votum.info:7443/';
    private const API_URL = self::BASE_URL . 'rest/api/latest/';
    private const TEMPO_API_URL = "rest/tempo-timesheets/3";
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

    public function getActiveSprint(): array
    {
        $responseArray = $this->jiraHttpClient->get(self::BOARD_URL . $this->getBoardId() . '/sprint');

        foreach ($responseArray['values'] as $sprint) {
            if ($sprint['state'] === 'active') {
                return $sprint;
            }
        }

        throw new JiraNoActiveSprintException('No active sprint found.');
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

    public function getTimeSpentByDate(\DateTimeImmutable $date): float
    {
        $dateString = $date->format('Y-m-d');
        $result = $this->jiraHttpClient->get(
            self::BASE_URL .
            self::TEMPO_API_URL .
            "/worklogs?dateFrom=$dateString&dateTo=$dateString"
        );

        $totalTimeSpentInSeconds = 0;
        foreach ($result as $worklog) {
            $totalTimeSpentInSeconds += (int)$worklog['timeSpentSeconds'];
        }

        return $totalTimeSpentInSeconds / 3600;
    }

    public function getCompleteWorklogByDate(\DateTimeImmutable $date): array
    {
        $dateString = $date->format('Y-m-d');
        $result = $this->jiraHttpClient->get(
            self::BASE_URL .
            self::TEMPO_API_URL .
            "/worklogs?dateFrom=$dateString&dateTo=$dateString"
        );

        $totalTimeSpentInSeconds = 0;
        $filteredWorklog = [];
        foreach ($result as $worklog) {
            $filteredWorklog['tickets'][] = [
                'number' => $worklog['issue']['key'],
                'comment' => $worklog['comment'],
                'bookedTime' => gmdate('H:i', $worklog['timeSpentSeconds'])
            ];

            $totalTimeSpentInSeconds += (int)$worklog['timeSpentSeconds'];
        }

        $filteredWorklog['totalBookedTime'] = gmdate('H:i', $totalTimeSpentInSeconds);

        return $filteredWorklog;
    }

    private function mapResponseToJiraIssueTransfer(array $issue): JiraIssueTransfer
    {
        $jiraIssueTransfer = $this->jiraIssueMapper->map($issue);
        $jiraIssueTransfer->url = self::BROWSE_URL . $jiraIssueTransfer->key;

        return $jiraIssueTransfer;
    }

    private function getBoardId(): string
    {
        return $this->configuration->getConfiguration(Configuration::BOARD_ID);
    }
}
