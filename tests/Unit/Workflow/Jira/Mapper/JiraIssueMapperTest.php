<?php

namespace Unit\Workflow\Jira\Mapper;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Transfers\JiraIssueTransfer;
use Turbine\Workflow\Workflow\Jira\Mapper\JiraIssueMapper;

class JiraIssueMapperTest extends TestCase
{
    /**
     * @dataProvider jiraIssueDataProvider
     *
     * @param array $jiraResponse
     * @param \Turbine\Workflow\Transfers\JiraIssueTransfer $expectedJiraIssueTransfer
     *
     * @return void
     */
    public function testMapIssueMapsJiraResponseArrayToJiraIssueTransfer(
        array $jiraResponse,
        JiraIssueTransfer $expectedJiraIssueTransfer
    ): void {
        $mapper = new JiraIssueMapper();
        self::assertEquals($expectedJiraIssueTransfer, $mapper->map($jiraResponse));
    }

    /**
     * @return array[]
     */
    public function jiraIssueDataProvider(): array
    {
        return [
            $this->getTestCase1Data(),
            $this->getTestCase2Data(),
            $this->getTestCase3Data(),
        ];
    }

    /**
     * @return array
     */
    private function getTestCase1Data(): array
    {
        $jiraResponseData = [
            'key' => 'BCM-12',
            'fields' => [
                'summary' => 'summary',
                'issuetype' => [
                    'subtask' => false,
                    'name' => 'improvement',
                ],
                'labels' => ['label'],
                'status' => [
                    'name' => 'status',
                ],
                'subtasks' => [],
                'created' => '2020-01-01',
                'description' => 'bla',
            ],
        ];

        $expectedJiraIssueTransfer = new JiraIssueTransfer();
        $expectedJiraIssueTransfer->key = 'BCM-12';
        $expectedJiraIssueTransfer->isSubTask = false;
        $expectedJiraIssueTransfer->summary = 'summary';
        $expectedJiraIssueTransfer->labels = ['label'];
        $expectedJiraIssueTransfer->type = 'improvement';
        $expectedJiraIssueTransfer->currentStatus = 'status';
        $expectedJiraIssueTransfer->createdAt = '2020-01-01';
        $expectedJiraIssueTransfer->assignee = 'Unassigned';
        $expectedJiraIssueTransfer->timeSpent = 'No time logged yet';
        $expectedJiraIssueTransfer->aggregateTimeSpent = null;
        $expectedJiraIssueTransfer->description = 'bla';
        $expectedJiraIssueTransfer->subTasks = [];

        return [
            'jiraResponse' => $jiraResponseData,
            'expectedJiraIssueTransfer' => $expectedJiraIssueTransfer,
        ];
    }

    /**
     * @return array
     */
    private function getTestCase2Data(): array
    {
        $jiraResponseData = [
            'key' => 'ABC-567',
            'fields' => [
                'summary' => 'summary sub-task',
                'issuetype' => [
                    'subtask' => true,
                    'name' => 'Sub Task'
                ],
                'labels' => ['label'],
                'status' => [
                    'name' => 'status',
                ],
                'created' => '2020-12-12',
                'assignee' => [
                    'displayName' => 'Assignee name',
                ],
                'timetracking' => [
                    'timeSpent' => '1h',
                ],
                'aggregatetimespent' => '12300',
                'description' => 'description',
                'subtasks' => [],
                'parent' => [
                    'key' => 'ABC-500',
                    'fields' => [
                        'summary' => 'parent summary',
                        'issuetype' => [
                            'name' => 'Story'
                        ],
                    ]
                ]
            ],
        ];

        $expectedJiraIssueTransfer = new JiraIssueTransfer();
        $expectedJiraIssueTransfer->key = 'ABC-567';
        $expectedJiraIssueTransfer->isSubTask = true;
        $expectedJiraIssueTransfer->summary = 'summary sub-task';
        $expectedJiraIssueTransfer->labels = ['label'];
        $expectedJiraIssueTransfer->type = 'Sub Task';
        $expectedJiraIssueTransfer->currentStatus = 'status';
        $expectedJiraIssueTransfer->createdAt = '2020-12-12';
        $expectedJiraIssueTransfer->assignee = 'Assignee name';
        $expectedJiraIssueTransfer->timeSpent = '1h';
        $expectedJiraIssueTransfer->aggregateTimeSpent = '3h 25m';
        $expectedJiraIssueTransfer->description = 'description';
        $expectedJiraIssueTransfer->parentIssueSummary = 'parent summary';
        $expectedJiraIssueTransfer->parentIssueKey = 'ABC-500';
        $expectedJiraIssueTransfer->parentIssueType = 'Story';
        $expectedJiraIssueTransfer->subTasks = [];

        return [
            'jiraResponse' => $jiraResponseData,
            'expectedJiraIssueTransfer' => $expectedJiraIssueTransfer,
        ];
    }

    /**
     * @return array
     */
    private function getTestCase3Data(): array
    {
        $jiraResponseData = [
            'key' => 'XXX-999',
            'fields' => [
                'summary' => 'summary story',
                'issuetype' => [
                    'subtask' => false,
                    'name' => 'Story'
                ],
                'labels' => ['different label'],
                'status' => [
                    'name' => 'sleepy',
                ],
                'created' => '2020-11-11',
                'description' => 'nice story',
                'subtasks' => [
                    'subtask1',
                    'subtask2',
                ],
            ],
        ];

        $expectedJiraIssueTransfer = new JiraIssueTransfer();
        $expectedJiraIssueTransfer->key = 'XXX-999';
        $expectedJiraIssueTransfer->isSubTask = false;
        $expectedJiraIssueTransfer->summary = 'summary story';
        $expectedJiraIssueTransfer->labels = ['different label'];
        $expectedJiraIssueTransfer->type = 'Story';
        $expectedJiraIssueTransfer->currentStatus = 'sleepy';
        $expectedJiraIssueTransfer->createdAt = '2020-11-11';
        $expectedJiraIssueTransfer->assignee = 'Unassigned';
        $expectedJiraIssueTransfer->timeSpent = 'No time logged yet';
        $expectedJiraIssueTransfer->aggregateTimeSpent = null;
        $expectedJiraIssueTransfer->description = 'nice story';
        $expectedJiraIssueTransfer->subTasks = ['subtask1', 'subtask2'];

        return [
            'jiraResponse' => $jiraResponseData,
            'expectedJiraIssueTransfer' => $expectedJiraIssueTransfer,
        ];
    }
}
