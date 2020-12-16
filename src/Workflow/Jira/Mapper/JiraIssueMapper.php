<?php

namespace Workflow\Workflow\Jira\Mapper;

use Workflow\Transfers\JiraIssueTransfer;

class JiraIssueMapper
{

    public function map(array $jiraResponse): JiraIssueTransfer
    {
        $jiraIssueTransfer = new JiraIssueTransfer();
        $jiraIssueTransfer->key = $jiraResponse['key'];
        $jiraIssueTransfer->summary = $jiraResponse['fields']['summary'];
        $jiraIssueTransfer->isSubTask = (bool)$jiraResponse['fields']['issuetype']['subtask'];
        $jiraIssueTransfer->labels = $jiraResponse['fields']['labels'] ?? [];

        return $jiraIssueTransfer;
    }
}