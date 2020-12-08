<?php

namespace Workflow\Workflow\Jira;

use Workflow\Client\JiraClient;

class IssueUpdater
{
    public function __construct(private JiraClient $jiraClient)
    {
    }

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
