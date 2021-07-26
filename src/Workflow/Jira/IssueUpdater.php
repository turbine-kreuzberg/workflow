<?php

declare(strict_types=1);

namespace Turbine\Workflow\Workflow\Jira;

use Turbine\Workflow\Client\JiraClient;
use Turbine\Workflow\Exception\JiraStateNotFoundException;

class IssueUpdater
{
    public function __construct(private JiraClient $jiraClient)
    {
    }

    public function bookTime(
        string $issue,
        string $comment,
        float $duration,
        string $date
    ): float {
        if ($duration < 15) {
            $duration *= 60;
        }

        $worklogEntry = [
            'comment' => $comment,
            'started' => $date,
            'timeSpentSeconds' => $duration * 60,
        ];

        $this->jiraClient->bookTime($issue, $worklogEntry);

        return $duration;
    }

    public function moveIssueToStatus(string $issue, string $targetState): void
    {
        $transitions = $this->jiraClient->getIssueTransitions($issue);
        foreach ($transitions['transitions'] as $transition) {
            if (mb_strtolower($transition['to']['name']) === mb_strtolower($targetState)) {
                $transitionId = $transition['id'];
                $this->jiraClient->transitionJiraIssue($issue, $transitionId);
                return;
            }
        }

        throw new JiraStateNotFoundException(sprintf('target state "%s" not available for issue', $targetState));
    }

    public function assignJiraIssueToUser(string $issue): void
    {
        $this->jiraClient->assignJiraIssueToUser($issue);
    }
}
