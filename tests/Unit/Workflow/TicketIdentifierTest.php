<?php

namespace Unit\Workflow;

use PHPUnit\Framework\TestCase;
use Workflow\Client\GitClient;
use Workflow\Workflow\TicketIdentifier;

class TicketIdentifierTest extends TestCase
{
    private string $oldBranch;

    protected function setUp(): void
    {
        $this->oldBranch = exec('git rev-parse --abbrev-ref HEAD');

    }

    protected function tearDown(): void
    {
        exec('git checkout ' . $this->oldBranch);
    }

    public function testExtractTicketIdFromCurrentBranch(): void
    {
        $gitClient = new GitClient();
        $ticketIdentifier = new TicketIdentifier();
        $testbranch = uniqid('BCM-100-', true);
        exec('git checkout -b '.$testbranch);
        $ticketIdentifier = $ticketIdentifier->extractFromBranchName($gitClient->getCurrentBranchName());
        exec('git checkout '. $this->oldBranch );
        exec('git branch -D '.$testbranch);
        self::assertEquals('BCM-100', $ticketIdentifier);
    }
}