<?php
namespace Turbine\Workflow\Workflow\Provider;

use Turbine\Workflow\Configuration;
use Turbine\Workflow\Workflow\Jira\IssueReader;

class FavouriteTicketChoicesProvider
{
    public function __construct(private Configuration $configuration, private IssueReader $issueReader)
    {
    }

    public function provide(): array
    {
        $favouriteTicketsFromEnvironment = $this->configuration->getConfiguration(
            Configuration::JIRA_FAVOURITE_TICKETS
        );

        if (empty($favouriteTicketsFromEnvironment)) {
            return [];
        }

        $issueArray = explode(',', $favouriteTicketsFromEnvironment);

        $favouriteIssues = $this->issueReader->getIssues($issueArray);

        $favouriteTickets = [];
        foreach ($favouriteIssues as $issue) {
            $favouriteTickets[$issue->key] = $issue->summary;
        }

        return $favouriteTickets;
    }
}
