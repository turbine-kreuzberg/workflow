<?php

namespace Workflow\Client;

class GitClient
{

    public function getCurrentBranchName() : string
    {
        return (string)exec('git rev-parse --abbrev-ref HEAD');
    }

}
