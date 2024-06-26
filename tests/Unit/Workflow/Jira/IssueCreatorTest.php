<?php

namespace Unit\Workflow\Jira;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Client\JiraClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Transfers\JiraIssueTransfer;
use Turbine\Workflow\Workflow\Jira\IssueCreator;

class IssueCreatorTest extends TestCase
{

    public function testCreateBugIssueBuildsIssueDataAndUsesJiraClientToCreateJiraIssue(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('createIssue')
            ->with(
                [
                'fields' => [
                    'summary' => 'issue-summary',
                    'description' => "h1.Symptom
issue-summary\n\nh1.How to reproduce\n\nh1.Expected Behavior\n\nh1.Details\n",
                    'issuetype' => [
                        'name' => 'Bug',
                    ],
                    'labels' => ['Bug'],
                    'components' => [
                        [
                            'name' => 'Bugs'
                        ],
                    ],
                    'project' => [
                        'key' => 'BCM'
                    ],
                    'customfield_10002' => 'ACCOUNT_ID',
                ],
                ]
            )
            ->willReturn(new JiraIssueTransfer());

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(
                ['JIRA_PROJECT_KEY'],
                ['JIRA_PROJECT_ACCOUNT_ID'],
            )
            ->willReturnOnConsecutiveCalls(
                'BCM',
                'ACCOUNT_ID'
            );

        $issueCreator = new IssueCreator($jiraClientMock, $configurationMock);

        $issueCreator->createIssue('issue-summary', 'bug');
    }

    public function testCreateImprovementIssueBuildsIssueDataAndUsesJiraClientToCreateJiraIssue(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);
        $jiraClientMock->expects(self::once())
            ->method('createIssue')
            ->with(
                [
                'fields' => [
                    'summary' => 'issue-summary',
                    'description' => "h1.Details\nissue-summary\n",
                    'issuetype' => [
                        'name' => 'Improvement',
                    ],
                    'labels' => ['Improvement'],
                    'components' => [
                        [
                            'name' => 'Improvements'
                        ],
                    ],
                    'project' => [
                        'key' => 'BCM'
                    ],
                    'customfield_10002' => 'ACCOUNT_ID',
                ],
                ]
            )
            ->willReturn(new JiraIssueTransfer());

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(
                ['JIRA_PROJECT_KEY'],
                ['JIRA_PROJECT_ACCOUNT_ID'],
            )
            ->willReturnOnConsecutiveCalls(
                'BCM',
                'ACCOUNT_ID'
            );

        $issueCreator = new IssueCreator($jiraClientMock, $configurationMock);

        $issueCreator->createIssue('issue-summary', 'improvement');
    }

    public function testCreateIssueForSprint(): void
    {
        $jiraClientMock = $this->createMock(JiraClient::class);

        $jiraClientMock->expects(self::once())
            ->method('getActiveSprint')
            ->willReturn(['id' => 123]);

        $jiraIssueTransfer = new JiraIssueTransfer();
        $jiraIssueTransfer->key = '123';
        $jiraClientMock->expects(self::once())
            ->method('createIssue')
            ->with(
                [
                    'fields' => [
                        'summary' => 'issue-summary',
                        'description' => "h1.Details\nissue-summary\n",
                        'issuetype' => [
                            'name' => 'Improvement',
                        ],
                        'labels' => ['Improvement'],
                        'components' => [
                            [
                                'name' => 'Improvements'
                            ],
                        ],
                        'project' => [
                            'key' => 'BCM'
                        ],
                        'customfield_10007' => '123',
                        'customfield_10002' => 'ACCOUNT_ID',
                    ],
                ]
            )
            ->willReturn($jiraIssueTransfer);

        $jiraClientMock->expects(self::exactly(2))
            ->method('transitionJiraIssue')
            ->withConsecutive(
                ['123', '821'],
                ['123', '711']
            );

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(
                ['JIRA_PROJECT_KEY'],
                ['JIRA_PROJECT_ACCOUNT_ID'],
            )
            ->willReturnOnConsecutiveCalls(
                'BCM',
                'ACCOUNT_ID'
            );
        $issueCreator = new IssueCreator($jiraClientMock, $configurationMock);

        $issueCreator->createIssueForSprint('issue-summary', 'improvement');
    }
}
