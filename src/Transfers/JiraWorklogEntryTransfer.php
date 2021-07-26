<?php

declare(strict_types=1);

namespace Turbine\Workflow\Transfers;

class JiraWorklogEntryTransfer
{
    public string $author;

    public int $timeSpentSeconds;

    public string $comment;

    public string $key;
}
