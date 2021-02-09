<?php

namespace Turbine\Workflow\Workflow\Jira;

use Turbine\Workflow\Client\JiraClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Exception\JiraNoWorklogException;
use Turbine\Workflow\Transfers\JiraIssueTransfer;
use Turbine\Workflow\Transfers\JiraIssueTransferCollection;
use Turbine\Workflow\Transfers\JiraWorklogEntryTransfer;
use Turbine\Workflow\Transfers\JiraWorklogsTransfer;

class IssueReader
{
    public function __construct(
        private JiraClient $jiraClient,
        private Configuration $configuration
    ) {
    }

    public function getLastTicketWorklog(string $issue): JiraWorklogEntryTransfer
    {
        $completeWorklog = $this->jiraClient->getWorkLog($issue);

        return $this->getLastWorkLogEntry($completeWorklog);
    }

    public function getIssues(array $issues): JiraIssueTransferCollection
    {
        $issueArray = [];
        foreach ($issues as $issue) {
            $issueArray[] = $this->jiraClient->getIssue($issue);
        }

        return new JiraIssueTransferCollection($issueArray);
    }

    private function getLastWorkLogEntry(array $completeWorklog): JiraWorklogEntryTransfer
    {
        if (empty($completeWorklog)) {
            throw new JiraNoWorklogException('No worklog entry found.');
        }

        if ($completeWorklog['total'] === 0) {
            throw new JiraNoWorklogException('No worklog entry found.');
        }

        $workLogEntryData = $completeWorklog['worklogs'][($completeWorklog['total'] - 1)];

        $jiraWorklogEntryTransfer = new JiraWorklogEntryTransfer();
        $jiraWorklogEntryTransfer->author = $workLogEntryData['author']['displayName'];
        $jiraWorklogEntryTransfer->comment = $workLogEntryData['comment'];
        $jiraWorklogEntryTransfer->timeSpentSeconds = $workLogEntryData['timeSpentSeconds'];

        return $jiraWorklogEntryTransfer;
    }

    public function getTimeSpentToday(): float
    {
        return $this->jiraClient->getTimeSpentByDate(new \DateTimeImmutable());
    }

    public function getCompleteWorklog(): JiraWorklogsTransfer
    {
        return $this->jiraClient->getCompleteWorklogByDate(new \DateTimeImmutable());
    }

    public function getIssue(string $issueKey): JiraIssueTransfer
    {
        if (is_numeric($issueKey)) {
            $issueKey = $this->configuration->getConfiguration(Configuration::JIRA_PROJECT_KEY) . '-' . $issueKey;
        }

        return $this->jiraClient->getIssue($issueKey);
    }
}
