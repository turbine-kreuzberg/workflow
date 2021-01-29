<?php

namespace Turbine\Workflow\Workflow\Provider;

use Turbine\Workflow\Workflow\Jira\IssueReader;

class WorklogChoicesProvider
{

    public function __construct(private IssueReader $issueReader, private CommitMessageProvider $commitMessageProvider)
    {
    }

    public function provide(string $issue): array
    {
        $jiraWorklogEntryTransfer = $this->issueReader->getLastTicketWorklog($issue);

        $worklogChoices = [
            $jiraWorklogEntryTransfer->comment,
            $this->commitMessageProvider->getLastCommitMessage()
        ];

        return $worklogChoices;
    }
}
