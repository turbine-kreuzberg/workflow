<?php

namespace Unit\Workflow\Jira\Mapper;

use PHPUnit\Framework\TestCase;
use Workflow\Transfers\JiraIssueTransfer;
use Workflow\Workflow\Jira\Mapper\JiraIssueMapper;

class JiraIssueMapperTest extends TestCase
{
    public function testMapIssueMapsJiraResponseArrayToJiraIssueTransfer(): void
    {
        $jiraResponse = [
            'key' => 'BCM-12',
            'fields' => [
                'summary' => 'summary',
                'issuetype' => [
                    'subtask' => false,
                ],
                    'labels' => ['label'],
                ],
        ];

        $expectedJiraIssueTransfer = new JiraIssueTransfer();
        $expectedJiraIssueTransfer->key = 'BCM-12';
        $expectedJiraIssueTransfer->isSubTask = false;
        $expectedJiraIssueTransfer->summary = 'summary';
        $expectedJiraIssueTransfer->labels = ['label'];
        $mapper = new JiraIssueMapper();
        self::assertEquals($expectedJiraIssueTransfer, $mapper->map($jiraResponse));
    }

}