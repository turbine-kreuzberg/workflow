<?php

namespace Unit\Client;

use PHPUnit\Framework\TestCase;
use Workflow\Client\GitClient;

class GitClientTest extends TestCase
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
        $client = new GitClient();
        $testBranch = uniqid('SUK-100-', true);
        exec('git checkout -b ' . $testBranch);
        $ticket = $client->extractTicketIdFromCurrentBranch();
        exec('git checkout ' . $this->oldBranch);
        exec('git branch -D ' . $testBranch);
        self::assertEquals('SUK-100', $ticket);
    }
}