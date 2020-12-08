<?php

namespace Unit\Workflow;

use PHPUnit\Framework\TestCase;
use Workflow\Client\GitClient;
use Workflow\Workflow\TicketIdentifier;

class TicketIdentifierTest extends TestCase
{

    public function testExtractTicketIdFromBranchName(): void
    {
        $ticketIdentifier = new TicketIdentifier();
        $testbranch = uniqid('BCM-100-', true);
        $ticketIdentifier = $ticketIdentifier->extractFromBranchName($testbranch);
        self::assertEquals('BCM-100', $ticketIdentifier);
    }
}