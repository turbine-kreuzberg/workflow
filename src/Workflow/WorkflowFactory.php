<?php

namespace Workflow\Workflow;

use Workflow\Client\ClientFactory;
use Workflow\Workflow\Jira\IssueReader;
use Workflow\Workflow\Jira\IssueUpdater;

class WorkflowFactory
{
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
        return new TicketIdentifier();
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

    private function getClientFactory(): ClientFactory
    {
        return new ClientFactory();
    }
}
