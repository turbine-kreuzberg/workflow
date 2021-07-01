<?php

declare(strict_types=1);

namespace Turbine\Workflow\Workflow\Provider;

use Turbine\Workflow\Exception\JiraNoWorklogException;
use Turbine\Workflow\Workflow\Jira\IssueReader;

class WorklogChoicesProvider
{
    public function __construct(private IssueReader $issueReader, private CommitMessageProvider $commitMessageProvider)
    {
    }

    public function provide(string $issue): array
    {
        $worklogChoices = [];
        try {
            $jiraWorklogEntryTransfer = $this->issueReader->getLastTicketWorklog($issue);
            $worklogChoices[] = $jiraWorklogEntryTransfer->comment;
        } catch (JiraNoWorklogException $jiraNoWorklogException) {
        }

        $worklogChoices[] = $this->commitMessageProvider->getLastCommitMessage();

        return $worklogChoices;
    }
}
