<?php

namespace Workflow\Client;

use Workflow\Workflow\TicketIdentifier;

class ClientFactory
{
    public function getGitClient(): GitClient
    {
        return new GitClient();
    }
}
