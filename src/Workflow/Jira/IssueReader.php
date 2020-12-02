<?php

namespace Workflow\Workflow\Jira;

use Workflow\Client\JiraClient;
use Workflow\Exception\JiraNoWorklogException;
use Workflow\Transfers\JiraIssueTransferCollection;
use Workflow\Transfers\JiraWorklogEntryTransfer;

class IssueReader
{
    private JiraClient $jiraClient;

    /**
     * @param \Workflow\Client\JiraClient $jiraClient
     */
    public function __construct(JiraClient $jiraClient)
    {
        $this->jiraClient = $jiraClient;
    }

    public function getLastTicketWorklog(string $issue): JiraWorklogEntryTransfer
    {
        $completeWorklog = $this->jiraClient->getWorkLog($issue);

        return $this->getLastWorkLogEntry($completeWorklog);
    }

    /**
     * @param array $issues
     *
     * @return \Workflow\Transfers\JiraIssueTransferCollection
     */
    public function getIssues(array $issues): JiraIssueTransferCollection
    {
        $issueArray = [];
        foreach ($issues as $issue) {
            $issueArray[] = $this->jiraClient->getIssue($issue);
        }

        return new JiraIssueTransferCollection($issueArray);
    }

    /**
     * @param array $completeWorklog
     *
     * @throws \Workflow\Exception\JiraNoWorklogException
     *
     * @return \Workflow\Transfers\JiraWorklogEntryTransfer
     */
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
