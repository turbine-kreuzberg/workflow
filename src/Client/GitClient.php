<?php

namespace Workflow\Client;

class GitClient
{
    private const JIRA_PROJECT_KEY = "JIRA_PROJECT_KEY";

    public function extractTicketIdFromCurrentBranch(): string
    {
        $matches = [];
        $branchName = $this->getCurrentBranchName();
        preg_match(sprintf("/(?'ticket'%s-\d{3,5})/", $this->getProjectKey()), $branchName, $matches);
        if (!isset($matches['ticket'])) {
            throw new \RuntimeException(sprintf('Ticket number not found in branch name %s', $branchName));
        }
        return $matches['ticket'];
    }

    private function getCurrentBranchName() : string
    {
        return exec('git rev-parse --abbrev-ref HEAD');
    }
    /**
     * @throws \Exception
     *
     * @return string
     */
    private function getProjectKey(): string
    {
        try {
            return getenv(self::JIRA_PROJECT_KEY);
        } catch (\Throwable $throwable) {
            throw new Exception('No project key set. Please see your ".env.dist" file how to create and use it.');
        }
    }
}
