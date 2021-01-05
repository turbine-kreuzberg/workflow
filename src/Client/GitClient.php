<?php

namespace Workflow\Client;

class GitClient
{

    public function getCurrentBranchName() : string
    {
        return (string)exec('git rev-parse --abbrev-ref HEAD');
    }

    public function getLastCommitMessage() : string
    {
        return (string)exec('git log -n 1 HEAD --format=%s');
    }

}
