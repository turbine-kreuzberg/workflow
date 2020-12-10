<?php

use Workflow\Configuration;
use Workflow\Workflow\Jira\IssueReader;

class FavouriteTicketProvider
{
    public function __construct(
        private Configuration $configuration,
        private IssueReader $issueReader,
    ) {
    }

    public function provide(): array
    {
        return [];
    }
}
