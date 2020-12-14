<?php

namespace Unit\Workflow;

use PHPUnit\Framework\TestCase;
use Workflow\Configuration;
use Workflow\Workflow\TicketIdentifier;

class TicketIdentifierTest extends TestCase
{

    public function testExtractTicketIdFromBranchName(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('getConfiguration')
            ->with('JIRA_PROJECT_KEY')
            ->willReturn('BCM');
        $ticketIdentifier = new TicketIdentifier($configurationMock);
        $testbranch = uniqid('BCM-100-', true);
        $ticketIdentifier = $ticketIdentifier->extractFromBranchName($testbranch);
        self::assertEquals('BCM-100', $ticketIdentifier);
    }
}