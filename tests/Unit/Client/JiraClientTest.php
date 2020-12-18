<?php

namespace Unit\Client;

use PHPUnit\Framework\TestCase;
use Workflow\Client\Http\AtlassianHttpClient;
use Workflow\Client\JiraClient;
use Workflow\Configuration;
use Workflow\Transfers\JiraIssueTransfer;
use Workflow\Workflow\Jira\Mapper\JiraIssueMapper;

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
}