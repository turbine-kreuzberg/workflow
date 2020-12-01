<?php

namespace Workflow\Client;

class GitClient
{
    /**
     * @throws \Exception
     *
     * @return string
     */
    public function getTicketFromCurrentBranch(): string
    {
        $matches = [];
        $branchName = $this->getCurrentBranchName();
        preg_match("/(?'ticket'SUK-\d{3,5})/", $branchName, $matches);
        if (!isset($matches['ticket'])) {
            throw new \RuntimeException(sprintf('Ticket number not found in branch name %s', $branchName));
        }
        return $matches['ticket'];
    }

    private function getCurrentBranchName() : string
    {
        return exec('git rev-parse --abbrev-ref HEAD');
    }
}
