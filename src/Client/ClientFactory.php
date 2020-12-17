<?php

namespace Workflow\Client;

use GuzzleHttp\Client;
use Workflow\Client\Http\AtlassianHttpClient;
use Workflow\Configuration;
use Workflow\Workflow\Jira\Mapper\JiraIssueMapper;

class ClientFactory
{
    private ?JiraClient $jiraClient = null;

    public function getGitClient(): GitClient
    {
        return new GitClient();
    }

    public function getJiraClient(): JiraClient
    {
        if ($this->jiraClient === null) {
            $this->jiraClient = new JiraClient(
                new AtlassianHttpClient(new Configuration(), new Client()),
                new Configuration(),
                new JiraIssueMapper()
            );
        }

        return $this->jiraClient;
    }
}
