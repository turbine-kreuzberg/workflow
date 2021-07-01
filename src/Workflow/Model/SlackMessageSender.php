<?php

declare(strict_types=1);

namespace Turbine\Workflow\Workflow\Model;

use Turbine\Workflow\Client\SlackClient;

class SlackMessageSender
{
    public function __construct(private SlackClient $slackClient)
    {
    }

    public function send(string $text): void
    {
        $this->slackClient->sendMessageUsingWebhook($text);
    }
}
