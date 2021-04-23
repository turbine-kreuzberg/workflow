<?php

namespace Unit\Workflow\Model;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Client\SlackClient;
use Turbine\Workflow\Workflow\Model\SlackMessageSender;

class SlackMessageSenderTest extends TestCase
{
    public function testSend(): void
    {
        $slackClientMock = $this->createMock(SlackClient::class);
        $slackClientMock->expects(self::once())
            ->method('sendMessageUsingWebhook')
            ->with('hello mars');

        $slackMessageSender = new SlackMessageSender($slackClientMock);

        $slackMessageSender->send('hello mars');
    }
}
