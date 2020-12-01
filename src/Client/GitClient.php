<?php

namespace Workflow\Client;

class GitClient
{

    public function getCurrentBranchName() : string
    {
        return exec('git rev-parse --abbrev-ref HEAD');
    }

}
