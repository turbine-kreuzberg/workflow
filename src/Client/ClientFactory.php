<?php

namespace Turbine\Workflow\Client;

use GuzzleHttp\Client;
use Turbine\Workflow\Client\Http\AtlassianHttpClient;
use Turbine\Workflow\Client\Http\GitlabHttpClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Workflow\Jira\Mapper\JiraIssueMapper;

class ClientFactory
{
    private ?JiraClient $jiraClient = null;

    public function getGitClient(): GitClient
    {
        return new GitClient();
    }

    public function getGitLabClient(): GitlabClient
    {
        return new GitlabClient(
            new GitlabHttpClient(
                new Configuration(),
            ),
            new Configuration()
        );
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
