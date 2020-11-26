<?php

namespace Workflow\Client;

class ClientFactory
{
    public function getGitClient(): GitClient
    {
        return new GitClient();
    }
}
