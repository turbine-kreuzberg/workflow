<?php

namespace Workflow\Workflow\Jira;

use Workflow\Client\JiraClient;

class IssueUpdater
{
    private JiraClient $jiraClient;

    /**
     * @param \Workflow\Client\JiraClient $jiraClient
     */
    public function __construct(JiraClient $jiraClient)
    {
        $this->jiraClient = $jiraClient;
    }

    /**
     * @param string $issue
     * @param string $comment
     * @param int $minutes
     * @param string $date
     *
     * @return void
     */
    public function bookTime(
        string $issue,
        string $comment,
        int $minutes,
        string $date
    ): void {
        $worklogEntry = [
            'comment' => $comment,
            'started' => $date,
            'timeSpentSeconds' => $minutes * 60,
        ];

        $this->jiraClient->bookTime($issue, $worklogEntry);
    }
}
