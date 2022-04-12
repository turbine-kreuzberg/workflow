<?php

declare(strict_types=1);

namespace Turbine\Workflow\Workflow\Jira\Mapper;

use Turbine\Workflow\Transfers\JiraIssueTransfer;
use Turbine\Workflow\Workflow\Formatter\HumanReadableDateIntervalFormatter;

class JiraIssueMapper
{
    public function __construct(
        private HumanReadableDateIntervalFormatter $humanReadableDateIntervalFormatter
    ) {
    }

    public function map(array $jiraResponse): JiraIssueTransfer
    {
        $jiraIssueTransfer = new JiraIssueTransfer();

        $jiraIssueTransfer->key = $jiraResponse['key'];

        $jiraIssueFields = $jiraResponse['fields'];

        $jiraIssueTransfer->summary = $jiraIssueFields['summary'];
        $jiraIssueTransfer->isSubTask = $jiraIssueFields['issuetype']['subtask'];
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

    private function toHumanReadableTime(?int $timeInSeconds): ?string
    {
        if ($timeInSeconds === null) {
            return null;
        }

        return $this->humanReadableDateIntervalFormatter->format($timeInSeconds);
    }
}
