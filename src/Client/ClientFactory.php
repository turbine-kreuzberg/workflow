<?php

namespace Workflow\Client;

use Workflow\Client\Http\AtlassianHttpClient;
use Workflow\Configuration;
use Workflow\Workflow\Jira\Mapper\JiraIssueMapper;

class ClientFactory
{
    public function getGitClient(): GitClient
    {
        return new GitClient();
    }

    public function getJiraClient(): JiraClient
    {
        return new JiraClient(new AtlassianHttpClient(), new Configuration(), new JiraIssueMapper());
    }
}
