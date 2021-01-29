<?php
declare(strict_types = 1);

namespace Turbine\Workflow\Workflow\Provider;

use Turbine\Workflow\Client\GitClient;

class CommitMessageProvider
{
    public function __construct(
        private GitClient $gitClient
    ) {
    
    }

    public function getLastCommitMessage() : string
    {
        return $this->gitClient->getLastCommitMessage();
    }
}