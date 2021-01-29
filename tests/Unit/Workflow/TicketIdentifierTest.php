<?php

namespace Unit\Workflow;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Workflow\TicketIdentifier;

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
        $testBranch = uniqid('BCM-100-', true);
        $ticketIdentifier = $ticketIdentifier->extractFromBranchName($testBranch);
        self::assertEquals('BCM-100', $ticketIdentifier);
    }

    public function testTicketNumberNotFoundInBranchNameThrowsException(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('getConfiguration')
            ->with('JIRA_PROJECT_KEY')
            ->willReturn('unknown project key');
        $ticketIdentifier = new TicketIdentifier($configurationMock);
        $testBranch = uniqid('BCM-100-', true);
        $this->expectException(RuntimeException::class);
        $ticketIdentifier->extractFromBranchName($testBranch);
    }

    public function testExtractTicketIdFromUnprefixedBranchName(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(2))
            ->method('getConfiguration')
            ->with('JIRA_PROJECT_KEY')
            ->willReturn('BCM');
        $ticketIdentifier = new TicketIdentifier($configurationMock);
        $testBranch = uniqid('100-', true);
        $ticketIdentifier = $ticketIdentifier->extractFromBranchName($testBranch);
        self::assertEquals('BCM-100', $ticketIdentifier);
    }
}
