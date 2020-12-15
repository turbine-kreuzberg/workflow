<?php

namespace Unit\Client;

use PHPUnit\Framework\TestCase;
use Workflow\Client\Http\AtlassianHttpClient;
use Workflow\Client\JiraClient;
use Workflow\Configuration;

class JiraClientTest extends TestCase
{
    public function testGetWorklogUsesHttpClientToCallJiraWorklogEndpoint(): void
    {
        $jiraHttpClientMock = $this->createMock(AtlassianHttpClient::class);
        $jiraHttpClientMock->expects(self::once())
            ->method('get')
            ->with('https://jira.votum.info:7443/rest/api/latest/issue/BCM-12/worklog')
            ->willReturn(['some-worklog']);

        $jiraClient = new JiraClient($jiraHttpClientMock, $this->createMock(Configuration::class));
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

        $jiraClient = new JiraClient($jiraHttpClientMock, $this->createMock(Configuration::class));
        $jiraClient->bookTime('BCM-12', ['worklogEntry']);
    }

}