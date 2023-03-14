<?php
declare(strict_types=1);

namespace Unit\Client;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Client\Http\AtlassianHttpClient;
use Turbine\Workflow\Client\JiraClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Workflow\Jira\Mapper\JiraIssueMapper;

/**
 * @group DORA
 */
class JiraClientBugTicketsTest extends TestCase
{

    public function testGetRecentBugTickets()
    {
        $atlassianHttpClient = new AtlassianHttpClient(new Configuration(), new Client());
        $configuration = new Configuration();

        $jiraIssueMapper = $this->createMock(JiraIssueMapper::class);
        $jiraClient = new JiraClient($atlassianHttpClient, $configuration, $jiraIssueMapper);

//        $this->assertSame([], $jiraClient->getActiveSprint());
        $issues = $jiraClient->getRecentBugTickets()['issues'];

        $statuses = [];
        foreach ($issues as $issue) {
            $statuses[$issue['fields']['status']['name']] =$issue['fields']['status']['name'];
        }
        $this->assertSame(12, ($issues));

        $this->assertSame(12, count($jiraClient->getRecentBugTickets()['issues']));
    }
}
