<?php

namespace Workflow\Workflow;

use Workflow\Client\ClientFactory;

class WorkflowFactory
{
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

    private function getClientFactory(): ClientFactory
    {
        return new ClientFactory();
    }
}
