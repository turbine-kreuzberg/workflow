<?php

namespace Turbine\Workflow\Client;

class GitClient
{
    public function getCurrentBranchName() : string
    {
        return (string)exec('git rev-parse --abbrev-ref HEAD');
    }

    public function getLastCommitMessage() : string
    {
        return (string)exec('git log -n 1 --format=%s');
    }

    public function createBranchOnTopOf(string $sourceBranch, string $branchName): void
    {
        exec('git checkout ' . $sourceBranch);
        exec('git pull --rebase');
        exec('git checkout -b ' . $branchName);
    }

    public function getGitLog(): string
    {
        $gitLog = [];
        exec('git log -1 --pretty=%B', $gitLog);
        $gitLog = implode(PHP_EOL, $gitLog);

        return $gitLog;
    }
}
