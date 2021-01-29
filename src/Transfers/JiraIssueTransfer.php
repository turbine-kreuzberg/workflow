<?php

namespace Turbine\Workflow\Transfers;

class JiraIssueTransfer
{
    public string $key;

    public string $summary;

    public bool $isSubTask;

    public string $url;

    public array $labels;
}
