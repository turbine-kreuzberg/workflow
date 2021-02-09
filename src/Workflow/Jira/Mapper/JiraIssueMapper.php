<?php

namespace Turbine\Workflow\Workflow\Jira\Mapper;

use DateTime;
use Turbine\Workflow\Transfers\JiraIssueTransfer;

class JiraIssueMapper
{

    public function map(array $jiraResponse): JiraIssueTransfer
    {
        $jiraIssueTransfer = new JiraIssueTransfer();

        $jiraIssueTransfer->key = $jiraResponse['key'];

        $jiraIssueFields = $jiraResponse['fields'];

        $jiraIssueTransfer->summary = $jiraIssueFields['summary'];
        $jiraIssueTransfer->isSubTask = (bool)$jiraIssueFields['issuetype']['subtask'];
        $jiraIssueTransfer->labels = $jiraIssueFields['labels'] ?? [];

        $jiraIssueTransfer->type = $jiraIssueFields['issuetype']['name'];
        $jiraIssueTransfer->currentStatus = $jiraIssueFields['status']['name'];
        $jiraIssueTransfer->createdAt = $jiraIssueFields['created'];
        $jiraIssueTransfer->assignee = $jiraIssueFields['assignee']['displayName'] ?? 'Unassigned';
        $jiraIssueTransfer->timeSpent = $jiraIssueFields['timetracking']['timeSpent'] ?? 'No time logged yet';
        $jiraIssueTransfer->aggregateTimeSpent = $this->toHumanReadableTime(
            $jiraIssueFields['aggregatetimespent'] ?? null
        );
        $jiraIssueTransfer->description = $jiraIssueFields['description'];
        $jiraIssueTransfer->subTasks = $jiraIssueFields['subtasks'];

        if (isset($jiraIssueFields['parent'])) {
            $jiraIssueTransfer->parentIssueType = $jiraIssueFields['parent']['fields']['issuetype']['name'];
            $jiraIssueTransfer->parentIssueSummary = $jiraIssueFields['parent']['fields']['summary'];
            $jiraIssueTransfer->parentIssueKey = $jiraIssueFields['parent']['key'];
        }

        return $jiraIssueTransfer;
    }

    /**
     * @param string|null $aggregateTimeSpent
     *
     * @return string|null
     */
    private function toHumanReadableTime(?string $aggregateTimeSpent): ?string
    {
        if ($aggregateTimeSpent === null) {
            return null;
        }

        $timeDiff = date_diff(new DateTime('@0'), new DateTime('@' . $aggregateTimeSpent), true);

        $humanReadableTime = [];
        foreach ((array)$timeDiff as $timeKey => $value) {
            if ($value > 0) {
                if ($timeKey === 'i') {
                    $timeKey = 'm';
                }

                $humanReadableTime[] = $value . $timeKey;
            }
        }

        return implode(' ', $humanReadableTime);
    }
}
