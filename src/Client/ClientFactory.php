<?php

namespace Turbine\Workflow\Client;

use GuzzleHttp\Client;
use RuntimeException;
use Turbine\Workflow\Client\Http\AtlassianHttpClient;
use Turbine\Workflow\Client\Http\GitlabHttpClient;
use Turbine\Workflow\Client\Http\SlackHttpClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Workflow\Formatter\HumanReadableDateIntervalFormatter;
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
                new Client()
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
                new JiraIssueMapper(
                    new HumanReadableDateIntervalFormatter()
                )
            );
        }

        return $this->jiraClient;
    }

    public function getSlackClient(): SlackClient
    {
        return new SlackClient(new SlackHttpClient(new Client()), new Configuration());
    }
}
