<?php

namespace Unit\Client;

use PHPUnit\Framework\TestCase;
use Workflow\Client\GitClient;

class GitClientTest extends TestCase
{
    public function testGetTicketFromCurrentBranch(): void
    {
        $client = new GitClient();
        $testbranch = uniqid('testbranch', true);
        exec('git switch -c '.$testbranch);
        $ticket = $client->getTicketFromCurrentBranch();
        exec('git branch -D '.$testbranch);
        self::assertEquals('SUK-test', $ticket);
    }
}