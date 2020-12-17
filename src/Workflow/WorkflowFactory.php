<?php

namespace Workflow\Workflow;

use Workflow\Client\ClientFactory;
use Workflow\Configuration;
use Workflow\Workflow\Jira\IssueCreator;
use Workflow\Workflow\Jira\IssueReader;
use Workflow\Workflow\Jira\IssueUpdater;
use Workflow\Workflow\Provider\FavouriteTicketChoicesProvider;

class WorkflowFactory
{
    private ?Configuration $configuration = null;

    private ?ClientFactory $clientFactory = null;

    private ?IssueReader $issueReader = null;

    private ?IssueUpdater $issueUpdater = null;

    public function getBookTime(): BookTime
    {
        return new BookTime(
            $this->getClientFactory()->getGitClient(),
            $this->getTicketIdentifier()
        );
    }

    public function getTicketIdentifier(): TicketIdentifier
    {
        return new TicketIdentifier($this->createConfiguration());
    }

    public function createJiraIssueReader(): IssueReader
    {
        if ($this->issueReader === null) {
            $this->issueReader = new IssueReader(
                $this->getClientFactory()->getJiraClient()
            );
        }

        return $this->issueReader;
    }

    public function createJiraIssueUpdater(): IssueUpdater
    {
        if ($this->issueUpdater === null) {
            $this->issueUpdater = new IssueUpdater(
                $this->getClientFactory()->getJiraClient()
            );
        }

        return $this->issueUpdater;
    }

    public function createFavouriteTicketChoicesProvider(): FavouriteTicketChoicesProvider
    {
        return new FavouriteTicketChoicesProvider(
            $this->createConfiguration(),
            $this->createJiraIssueReader()
        );
    }

    public function createJiraIssueCreator(): IssueCreator
    {
        return new IssueCreator($this->getClientFactory()->getJiraClient(), $this->createConfiguration());
    }

    private function getClientFactory(): ClientFactory
    {
        return new ClientFactory();
    }

    private function createConfiguration(): Configuration
    {
        if ($this->configuration === null) {
            $this->configuration = new Configuration();
        }

        return $this->configuration;
    }
}
