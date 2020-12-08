<?php
declare(strict_types = 1);


namespace Workflow\Workflow;

use Workflow\Configuration;

class TicketIdentifier
{

    public function extractFromBranchName(string $branchName): string
    {
        $matches = [];
        preg_match(sprintf("/(?'ticket'%s-\d{3,5})/", Configuration::getProjectKey()), $branchName, $matches);
        if (!isset($matches['ticket'])) {
            throw new \RuntimeException(sprintf('Ticket number not found in branch name %s', $branchName));
        }
        return $matches['ticket'];
    }

}