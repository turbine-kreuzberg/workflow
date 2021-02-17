<?php
declare(strict_types = 1);


namespace Turbine\Workflow\Workflow;

use Turbine\Workflow\Configuration;

class TicketIdentifier
{
    public function __construct(private Configuration $configuration)
    {
    }

    public function extractFromBranchName(string $branchName): string
    {
        $matches = [];
        preg_match(
            sprintf("/(?'ticket'%s-\d{1,5})/", $this->configuration->get(Configuration::JIRA_PROJECT_KEY)),
            $branchName,
            $matches
        );

        if (isset($matches['ticket'])) {
            return $matches['ticket'];
        }

        preg_match(
            "/^(?'ticket'\d{1,5})-/",
            $branchName,
            $matches
        );

        if (isset($matches['ticket'])) {
            return $this->configuration->get(Configuration::JIRA_PROJECT_KEY) . '-' . $matches['ticket'];
        }

        throw new \RuntimeException(sprintf('Ticket number not found in branch name %s', $branchName));
    }

}
