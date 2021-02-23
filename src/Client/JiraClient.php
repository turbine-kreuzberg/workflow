<?php

namespace Turbine\Workflow\Client;

use DateTimeImmutable;
use Turbine\Workflow\Client\Http\AtlassianHttpClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Exception\JiraNoActiveSprintException;
use Turbine\Workflow\Transfers\JiraIssueTransfer;
use Turbine\Workflow\Transfers\JiraWorklogEntryCollectionTransfer;
use Turbine\Workflow\Transfers\JiraWorklogEntryTransfer;
use Turbine\Workflow\Transfers\JiraWorklogsTransfer;
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
        $issue = $this->normalizeIssueNumber($issue);

        return $this->jiraHttpClient->get(static::ISSUE_URL . $issue . '/' . 'transitions');
    }

    public function transitionJiraIssue(string $issue, string $transitionId): void
    {
        $issue = $this->normalizeIssueNumber($issue);

        $transitionData = ['transition' => ['id' => $transitionId]];
        $transitionUrl = self::ISSUE_URL . $issue . '/transitions';
        $this->jiraHttpClient->post($transitionUrl, $transitionData);
    }

    public function assignJiraIssueToUser(string $issue): void
    {
        $assigneeData = ['name' => $this->getUsername()];
        $issueUrl = self::ISSUE_URL . $this->normalizeIssueNumber($issue) . '/assignee';
        $this->jiraHttpClient->put($issueUrl, $assigneeData);
    }

    private function getUsername(): string
    {
        return $this->configuration->get(Configuration::JIRA_USERNAME);
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
        $issueCall = self::ISSUE_URL . $this->normalizeIssueNumber($issue);;

        return $this->mapResponseToJiraIssueTransfer(
            $this->jiraHttpClient->get($issueCall)
        );
    }

    public function getTimeSpentByDate(DateTimeImmutable $date): float
    {
        $result = $this->getWorklogByDate($date);

        $totalTimeSpentInSeconds = 0;
        foreach ($result as $worklog) {
            $totalTimeSpentInSeconds += (int)$worklog['timeSpentSeconds'];
        }

        return $totalTimeSpentInSeconds / 3600;
    }

    public function getCompleteWorklogByDate(DateTimeImmutable $date): JiraWorklogsTransfer
    {
        $result = $this->getWorklogByDate($date);

        $totalTimeSpentInSeconds = 0;
        $filteredWorklog = [];
        foreach ($result as $worklog) {
            $jiraWorklogEntryTransfer = new JiraWorklogEntryTransfer();
            $jiraWorklogEntryTransfer->key = $worklog['issue']['key'];
            $jiraWorklogEntryTransfer->comment = $worklog['comment'];
            $jiraWorklogEntryTransfer->timeSpentSeconds = $worklog['timeSpentSeconds'];
            $filteredWorklog[] = $jiraWorklogEntryTransfer;

            $totalTimeSpentInSeconds += (int)$worklog['timeSpentSeconds'];
        }
        $jiraIssueTransferCollection = new JiraWorklogEntryCollectionTransfer($filteredWorklog);

        $jiraWorklogsTransfer = new JiraWorklogsTransfer();
        $jiraWorklogsTransfer->jiraWorklogEntryCollection = $jiraIssueTransferCollection;
        $jiraWorklogsTransfer->totalSpentTime = $totalTimeSpentInSeconds;

        return $jiraWorklogsTransfer;
    }

    private function mapResponseToJiraIssueTransfer(array $issue): JiraIssueTransfer
    {
        $jiraIssueTransfer = $this->jiraIssueMapper->map($issue);
        $jiraIssueTransfer->url = self::BROWSE_URL . $jiraIssueTransfer->key;

        return $jiraIssueTransfer;
    }

    private function getBoardId(): string
    {
        return $this->configuration->get(Configuration::BOARD_ID);
    }

    private function getWorklogByDate(DateTimeImmutable $date): array
    {
        $dateString = $date->format('Y-m-d');
        $result = $this->jiraHttpClient->get(
            self::BASE_URL .
            self::TEMPO_API_URL .
            "/worklogs?dateFrom=$dateString&dateTo=$dateString"
        );

        return $result;
    }

    private function normalizeIssueNumber(string $issueNumber): string
    {
        if (is_numeric($issueNumber)) {
            $issueNumber = $this->configuration->get(Configuration::JIRA_PROJECT_KEY) . '-' . $issueNumber;
        }

        return $issueNumber;
    }
}
