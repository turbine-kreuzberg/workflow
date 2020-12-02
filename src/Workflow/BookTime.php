<?php

namespace Workflow\Workflow;

use Workflow\Client\GitClient;

class BookTime
{
    private GitClient $gitClient;

    private TicketIdentifier $ticketIdentifier;

    public function __construct(
        GitClient $gitClient,
        TicketIdentifier $ticketIdentifier
    )
    {
        $this->gitClient = $gitClient;
        $this->ticketIdentifier = $ticketIdentifier;
    }

    public function extractTicketIdFromCurrentBranch(): string
    {
        $currentBranchName = $this->gitClient->getCurrentBranchName();

        return $this->ticketIdentifier->extractFromBranchName($currentBranchName);
    }

}
