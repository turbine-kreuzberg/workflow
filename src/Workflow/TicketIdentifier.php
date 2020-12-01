<?php
declare(strict_types = 1);


namespace Workflow\Workflow;

class TicketIdentifier
{
    private const JIRA_PROJECT_KEY = "JIRA_PROJECT_KEY";

    public function extractFromBranchName(string $branchName): string
    {
        $matches = [];
        preg_match(sprintf("/(?'ticket'%s-\d{3,5})/", $this->getProjectKey()), $branchName, $matches);
        if (!isset($matches['ticket'])) {
            throw new \RuntimeException(sprintf('Ticket number not found in branch name %s', $branchName));
        }
        return $matches['ticket'];
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
            throw new \RuntimeException('No project key set. Please see your ".env.dist" file how to create and use it.');
        }
    }
}