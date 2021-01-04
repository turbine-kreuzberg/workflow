<?php

namespace Workflow\Workflow\Provider;

use Workflow\Workflow\Jira\IssueReader;

class WorklogChoicesProvider
{

    public function __construct(private IssueReader $issueReader)
    {
    }

    public function provide(string $issue): array
    {
        $jiraWorklogEntryTransfer = $this->issueReader->getLastTicketWorklog($issue);

        $worklogChoices = [
            $jiraWorklogEntryTransfer->comment . ' (from ' . $jiraWorklogEntryTransfer->author . ')',

        ];

        return $worklogChoices;
    }
}
