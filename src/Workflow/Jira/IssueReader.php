<?php

namespace Workflow\Workflow\Jira;

use Workflow\Client\JiraClient;
use Workflow\Exception\JiraNoWorklogException;
use Workflow\Transfers\JiraIssueTransferCollection;
use Workflow\Transfers\JiraWorklogEntryTransfer;

class IssueReader
{
    public function __construct(private JiraClient $jiraClient)
    {
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
        $jiraWorklogEntryTransfer = new JiraWorklogEntryTransfer();
        if ($completeWorklog['total'] === 0) {
            throw new JiraNoWorklogException('no worklog entry found');
        }

        $workLogEntryData = $completeWorklog['worklogs'][($completeWorklog['total'] - 1)];

        $jiraWorklogEntryTransfer->author = $workLogEntryData['author']['displayName'];
        $jiraWorklogEntryTransfer->comment = $workLogEntryData['comment'];
        $jiraWorklogEntryTransfer->timeSpentSeconds = $workLogEntryData['timeSpentSeconds'];

        return $jiraWorklogEntryTransfer;
    }
}
