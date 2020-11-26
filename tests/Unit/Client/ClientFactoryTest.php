<?php

namespace Unit\Client;

use PHPUnit\Framework\TestCase;
use Workflow\Client\ClientFactory;
use Workflow\Client\GitClient;

class ClientFactoryTest extends TestCase
{
    public function testGetGitClient(): void
    {
        $clientFactory = new ClientFactory();
        $gitClient = $clientFactory->getGitClient();
        self::assertInstanceOf(GitClient::class, $gitClient);
    }
}
