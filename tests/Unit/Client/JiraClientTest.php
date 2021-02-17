<?php

namespace Unit\Client;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Client\Http\AtlassianHttpClient;
use Turbine\Workflow\Client\JiraClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Exception\JiraNoActiveSprintException;
use Turbine\Workflow\Transfers\JiraIssueTransfer;
use Turbine\Workflow\Transfers\JiraWorklogEntryCollectionTransfer;
use Turbine\Workflow\Transfers\JiraWorklogEntryTransfer;
use Turbine\Workflow\Transfers\JiraWorklogsTransfer;
use Turbine\Workflow\Workflow\Jira\Mapper\JiraIssueMapper;

class JiraClientTest extends TestCase
{
    public function testGetWorklogUsesHttpClientToCallJiraWorklogEndpoint(): void
    {
        $jiraHttpClientMock = $this->createMock(AtlassianHttpClient::class);
        $jiraHttpClientMock->expects(self::once())
            ->method('get')
            ->with('https://jira.votum.info:7443/rest/api/latest/issue/BCM-12/worklog')
            ->willReturn(['some-worklog']);

        $jiraClient = new JiraClient(
            $jiraHttpClientMock,
            $this->createMock(Configuration::class),
            $this->createMock(JiraIssueMapper::class)
        );
        $worklog = $jiraClient->getWorkLog('BCM-12');

        self::assertSame(['some-worklog'], $worklog);
    }

    public function testBookTimeUsesHttpClientToPostWorklogEntryToJira(): void
    {
        $jiraHttpClientMock = $this->createMock(AtlassianHttpClient::class);
        $jiraHttpClientMock->expects(self::once())
            ->method('post')
            ->with('https://jira.votum.info:7443/rest/api/latest/issue/BCM-12/worklog')
            ->willReturn(['some-worklog']);

        $jiraClient = new JiraClient(
            $jiraHttpClientMock,
            $this->createMock(Configuration::class),
            $this->createMock(JiraIssueMapper::class)
        );
        $jiraClient->bookTime('BCM-12', ['worklogEntry']);
    }

    public function testGetIssueUsesHttpClientToCallJiraWorklogEndpoint(): void
    {
        $jiraHttpClientMock = $this->createMock(AtlassianHttpClient::class);
        $jiraHttpClientMock->expects(self::once())
            ->method('get')
            ->with('https://jira.votum.info:7443/rest/api/latest/issue/BCM-12')
            ->willReturn(['some-response']);

        $jiraIssueMapperMock = $this->createMock(JiraIssueMapper::class);
        $jiraIssueTransfer = (new JiraIssueTransfer());
        $jiraIssueTransfer->key = 'BCM-12';

        $jiraIssueMapperMock
            ->expects(self::once())
            ->method('map')
            ->willReturn($jiraIssueTransfer);

        $jiraClient = new JiraClient(
            $jiraHttpClientMock,
            $this->createMock(Configuration::class),
            $jiraIssueMapperMock
        );
        $jiraIssueTransfer = $jiraClient->getIssue(issue: 'BCM-12');

        $expectedJiraIssueTransfer = new JiraIssueTransfer();
        $expectedJiraIssueTransfer->key = 'BCM-12';
        $expectedJiraIssueTransfer->url = 'https://jira.votum.info:7443/browse/BCM-12';

        self::assertEquals($expectedJiraIssueTransfer, $jiraIssueTransfer);
    }

    public function testCreateIssueUsesHttpClientToCallJiraIssueEndpoint(): void
    {
        $jiraHttpClientMock = $this->createMock(AtlassianHttpClient::class);
        $jiraHttpClientMock->expects(self::once())
            ->method('post')
            ->with('https://jira.votum.info:7443/rest/api/latest/issue/')
            ->willReturn(['key' => 'BCM-12']);

        $jiraHttpClientMock->expects(self::once())
            ->method('get')
            ->with('https://jira.votum.info:7443/rest/api/latest/issue/BCM-12')
            ->willReturn(['some-response']);

        $jiraIssueMapperMock = $this->createMock(JiraIssueMapper::class);
        $jiraIssueTransfer = (new JiraIssueTransfer());
        $jiraIssueTransfer->key = 'BCM-12';

        $jiraIssueMapperMock
            ->expects(self::once())
            ->method('map')
            ->willReturn($jiraIssueTransfer);

        $jiraClient = new JiraClient(
            $jiraHttpClientMock,
            $this->createMock(Configuration::class),
            $jiraIssueMapperMock
        );
        $jiraIssueTransfer = $jiraClient->createIssue(['issueData']);

        $expectedJiraIssueTransfer = new JiraIssueTransfer();
        $expectedJiraIssueTransfer->key = 'BCM-12';
        $expectedJiraIssueTransfer->url = 'https://jira.votum.info:7443/browse/BCM-12';

        self::assertEquals($expectedJiraIssueTransfer, $jiraIssueTransfer);
    }

    public function testUseHttpClientToAssignJiraIssueToUser(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('get')
            ->with('JIRA_USERNAME')
            ->willReturn('testUser');

        $jiraHttpClientMock = $this->createMock(AtlassianHttpClient::class);
        $jiraHttpClientMock->expects(self::once())
            ->method('put')
            ->with(
                'https://jira.votum.info:7443/rest/api/latest/issue/BCM-12/assignee',
                ['name' => 'testUser']
            )
            ->willReturn(['key' => 'BCM-12']);

        $jiraIssueMapperMock = $this->createMock(JiraIssueMapper::class);

        $jiraClient = new JiraClient(
            $jiraHttpClientMock,
            $configurationMock,
            $jiraIssueMapperMock
        );

        $jiraClient->assignJiraIssueToUser('BCM-12');
    }

    public function testUseHttpClientToGetTransitionsForGivenIssue(): void
    {
        $configurationMock = $this->createMock(Configuration::class);

        $jiraHttpClientMock = $this->createMock(AtlassianHttpClient::class);
        $jiraHttpClientMock->expects(self::once())
            ->method('get')
            ->with('https://jira.votum.info:7443/rest/api/latest/issue/BCM-12/transitions')
            ->willReturn(['transitions']);

        $jiraIssueMapperMock = $this->createMock(JiraIssueMapper::class);

        $jiraClient = new JiraClient(
            $jiraHttpClientMock,
            $configurationMock,
            $jiraIssueMapperMock
        );

        $jiraClient->getIssueTransitions('BCM-12');
    }

    public function testTransitionJiraIssue(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $jiraHttpClientMock = $this->createMock(AtlassianHttpClient::class);
        $jiraHttpClientMock->expects(self::once())
            ->method('post')
            ->with(
                'https://jira.votum.info:7443/rest/api/latest/issue/BCM-12/transitions',
                ['transition' => ['id' => '123']]
            );

        $jiraIssueMapperMock = $this->createMock(JiraIssueMapper::class);

        $jiraClient = new JiraClient(
            $jiraHttpClientMock,
            $configurationMock,
            $jiraIssueMapperMock
        );

        $jiraClient->transitionJiraIssue('BCM-12', '123');
    }

    public function testGetActiveSprintReturnsActiveSprint(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $jiraHttpClientMock = $this->createMock(AtlassianHttpClient::class);
        $jiraIssueMapperMock = $this->createMock(JiraIssueMapper::class);

        $configurationMock->expects(self::once())
            ->method('get')
            ->with('JIRA_BOARD_ID')
            ->willReturn('232');

        $jiraHttpClientMock->expects(self::once())
            ->method('get')
            ->with(
                'https://jira.votum.info:7443/rest/agile/1.0/board/232/sprint',
            )
            ->willReturn(
                [
                    'values' => [
                        [
                            'state' => 'not-active',
                        ],
                        [
                            'state' => 'active',
                        ],
                    ],
                ]
            );

        $jiraClient = new JiraClient(
            $jiraHttpClientMock,
            $configurationMock,
            $jiraIssueMapperMock
        );

        self::assertEquals(
            [
                'state' => 'active',
            ],
            $jiraClient->getActiveSprint()
        );
    }

    public function testGetActiveSprintReturnsThrowsExceptionIfNoActiveSprintForGivenBoardFound(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $jiraHttpClientMock = $this->createMock(AtlassianHttpClient::class);
        $jiraIssueMapperMock = $this->createMock(JiraIssueMapper::class);

        $configurationMock->expects(self::once())
            ->method('get')
            ->with('JIRA_BOARD_ID')
            ->willReturn('232');

        $jiraHttpClientMock->expects(self::once())
            ->method('get')
            ->with(
                'https://jira.votum.info:7443/rest/agile/1.0/board/232/sprint',
            )
            ->willReturn(
                [
                    'values' => [
                        [
                            'state' => 'not-active',
                        ],
                    ],
                ]
            );

        $jiraClient = new JiraClient(
            $jiraHttpClientMock,
            $configurationMock,
            $jiraIssueMapperMock
        );
        $this->expectException(JiraNoActiveSprintException::class);
        $jiraClient->getActiveSprint();
    }

    /**
     * @dataProvider provideWorklogsWithTimeSpent
     */
    public function testTimeSpentByDateSumsReturnedWorklogsTimeSpent(array $worklogs, float $expectedResult): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $jiraHttpClientMock = $this->createMock(AtlassianHttpClient::class);
        $jiraIssueMapperMock = $this->createMock(JiraIssueMapper::class);

        $jiraHttpClientMock->expects(self::once())
            ->method('get')
            ->with(
                'https://jira.votum.info:7443/rest/tempo-timesheets/3/worklogs?dateFrom=2020-01-01&dateTo=2020-01-01'
            )
            ->willReturn($worklogs);

        $jiraClient = new JiraClient(
            $jiraHttpClientMock,
            $configurationMock,
            $jiraIssueMapperMock
        );

        $timeSpentInHours = $jiraClient->getTimeSpentByDate(new DateTimeImmutable('01.01.2020'));

        self::assertEquals($expectedResult, $timeSpentInHours);
    }

    public function testCompleteWorklogByDateReturnsNoWorklogDataIfNoWorklogsLogged(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $jiraHttpClientMock = $this->createMock(AtlassianHttpClient::class);
        $jiraIssueMapperMock = $this->createMock(JiraIssueMapper::class);

        $jiraHttpClientMock->expects(self::once())
            ->method('get')
            ->with(
                'https://jira.votum.info:7443/rest/tempo-timesheets/3/worklogs?dateFrom=2020-01-01&dateTo=2020-01-01'
            )
            ->willReturn([]);

        $jiraClient = new JiraClient(
            $jiraHttpClientMock,
            $configurationMock,
            $jiraIssueMapperMock
        );
        $jiraWorklogs = $jiraClient->getCompleteWorklogByDate(new DateTimeImmutable('01.01.2020'));
        $expectedJiraWorkLogs = (new JiraWorklogsTransfer());
        $expectedJiraWorkLogs->jiraWorklogEntryCollection = new JiraWorklogEntryCollectionTransfer([]);
        $expectedJiraWorkLogs->totalSpentTime = 0;
        self::assertEquals($expectedJiraWorkLogs, $jiraWorklogs);

    }

    public function testCompleteWorklogByDateReturnsWorklogData(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $jiraHttpClientMock = $this->createMock(AtlassianHttpClient::class);
        $jiraIssueMapperMock = $this->createMock(JiraIssueMapper::class);

        $jiraHttpClientMock->expects(self::once())
            ->method('get')
            ->with(
                'https://jira.votum.info:7443/rest/tempo-timesheets/3/worklogs?dateFrom=2020-01-01&dateTo=2020-01-01'
            )
            ->willReturn(
                [
                [
                    'issue' => ['key' => 'ABC'],
                    'timeSpentSeconds' => 120,
                    'comment' => 'issue comment',
                ],
                ]
            );

        $jiraClient = new JiraClient(
            $jiraHttpClientMock,
            $configurationMock,
            $jiraIssueMapperMock
        );

        $jiraWorklogEntryTransfer = new JiraWorklogEntryTransfer();
        $jiraWorklogEntryTransfer->key = 'ABC';
        $jiraWorklogEntryTransfer->comment = 'issue comment';
        $jiraWorklogEntryTransfer->timeSpentSeconds = 120;

        $expectedJiraWorkLogs = new JiraWorklogsTransfer();
        $expectedJiraWorkLogs->jiraWorklogEntryCollection = new JiraWorklogEntryCollectionTransfer(
            [
            $jiraWorklogEntryTransfer
            ]
        );
        $expectedJiraWorkLogs->totalSpentTime = 120;

        $jiraWorklogs = $jiraClient->getCompleteWorklogByDate(new DateTimeImmutable('01.01.2020'));
        self::assertEquals($expectedJiraWorkLogs, $jiraWorklogs);
    }

    public function testCompleteWorklogByDateReturnsWorklogDataOfMultipleWorklogItems(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $jiraHttpClientMock = $this->createMock(AtlassianHttpClient::class);
        $jiraIssueMapperMock = $this->createMock(JiraIssueMapper::class);

        $jiraHttpClientMock->expects(self::once())
            ->method('get')
            ->with(
                'https://jira.votum.info:7443/rest/tempo-timesheets/3/worklogs?dateFrom=2020-01-01&dateTo=2020-01-01'
            )
            ->willReturn(
                [
                [
                    'issue' => ['key' => 'ABC'],
                    'timeSpentSeconds' => 120,
                    'comment' => 'issue comment',
                ],
                [
                    'issue' => ['key' => 'CDE'],
                    'timeSpentSeconds' => 90,
                    'comment' => 'issue comment 2',
                ],
                ]
            );

        $jiraClient = new JiraClient(
            $jiraHttpClientMock,
            $configurationMock,
            $jiraIssueMapperMock
        );

        $jiraWorklogEntryTransfer1 = new JiraWorklogEntryTransfer();
        $jiraWorklogEntryTransfer1->key = 'ABC';
        $jiraWorklogEntryTransfer1->comment = 'issue comment';
        $jiraWorklogEntryTransfer1->timeSpentSeconds = 120;

        $jiraWorklogEntryTransfer2 = new JiraWorklogEntryTransfer();
        $jiraWorklogEntryTransfer2->key = 'CDE';
        $jiraWorklogEntryTransfer2->comment = 'issue comment 2';
        $jiraWorklogEntryTransfer2->timeSpentSeconds = 90;

        $expectedJiraWorkLogs = new JiraWorklogsTransfer();
        $expectedJiraWorkLogs->jiraWorklogEntryCollection = new JiraWorklogEntryCollectionTransfer(
            [
            $jiraWorklogEntryTransfer1,
            $jiraWorklogEntryTransfer2,
            ]
        );
        $expectedJiraWorkLogs->totalSpentTime = 210;

        $jiraWorklogs = $jiraClient->getCompleteWorklogByDate(new DateTimeImmutable('01.01.2020'));
        self::assertEquals($expectedJiraWorkLogs, $jiraWorklogs);
    }

    public function provideWorklogsWithTimeSpent(): array
    {
        return [
            'no worklogs return no time spent' => [
                'worklogs' => [],
                'expectedResult ' => 0.0,
            ],
            'worklogs with no time spent return no time spent' => [
                'worklogs' => [['timeSpentSeconds' => 0]],
                'expectedResult ' => 0.0,
            ],
            'one worklog with time spent' => [
                'worklogs' => [['timeSpentSeconds' => 7200]],
                'expectedResult ' => 2.0,
            ],
            'multiple worklog with time spent' => [
                'worklogs' => [
                    ['timeSpentSeconds' => 7200],
                    ['timeSpentSeconds' => 3600],
                ],
                'expectedResult ' => 3.0,
            ],
        ];
    }
}
