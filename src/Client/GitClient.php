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
        return (string)exec('git log --format=%B -n 1 HEAD');
    }

}
