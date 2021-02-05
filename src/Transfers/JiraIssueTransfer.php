<?php

namespace Turbine\Workflow\Transfers;

class JiraIssueTransfer
{
    public string $key;

    public string $summary;

    public bool $isSubTask;

    public string $url;

    public array $labels;

    public string $type;

    public ?string $parentIssueKey;

    public ?string $parentIssueType;

    public ?string $parentIssueSummary;

    public string $currentStatus;

    public string $createdAt;

    public ?string $assignee;

    public ?string $timeSpent;

    public ?string $aggregateTimeSpent;

    public ?string $description;

    public array $subTasks;
}
