<?php

declare(strict_types=1);

namespace Turbine\Workflow\Workflow;

use Turbine\Workflow\Client\GitClient;

class TicketIdProvider
{
    private GitClient $gitClient;

    private TicketIdentifier $ticketIdentifier;

    public function __construct(
        GitClient $gitClient,
        TicketIdentifier $ticketIdentifier
    ) {
        $this->gitClient = $gitClient;
        $this->ticketIdentifier = $ticketIdentifier;
    }

    public function extractTicketIdFromCurrentBranch(): string
    {
        $currentBranchName = $this->gitClient->getCurrentBranchName();

        return $this->ticketIdentifier->extractFromBranchName($currentBranchName);
    }
}
