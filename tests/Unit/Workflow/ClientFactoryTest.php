<?php
namespace Unit\Workflow;

use PHPUnit\Framework\TestCase;
use Workflow\Client\ClientFactory;
use Workflow\Client\GitClient;
use Workflow\Client\JiraClient;

class ClientFactoryTest extends TestCase
{
    public function testGetGitClient(): void
    {
        $clientFactory = new ClientFactory();
        $gitClient = $clientFactory->getGitClient();
        self::assertInstanceOf(GitClient::class, $gitClient);
    }

    public function testGetJiraClient(): void
    {
        $clientFactory = new ClientFactory();
        $gitClient = $clientFactory->getJiraClient();
        self::assertInstanceOf(JiraClient::class, $gitClient);
    }
}
