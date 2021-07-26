<?php

declare(strict_types=1);

namespace Turbine\Workflow\Transfers;

class JiraWorklogsTransfer
{
    public JiraWorklogEntryCollectionTransfer $jiraWorklogEntryCollection;

    public int $totalSpentTime;
}
