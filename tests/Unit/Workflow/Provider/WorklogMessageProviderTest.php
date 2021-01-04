<?php
declare(strict_types = 1);


namespace Unit\Workflow\Provider;

use PHPUnit\Framework\TestCase;
use Workflow\Client\GitClient;
use Workflow\Workflow\Provider\CommitMessageProvider;

class WorklogMessageProviderTest extends TestCase
{
    public function testProposeLastCommitMessage() : void
    {
        $expectedLastCommitMessage = 'git it from git';
        $gitClientMock = $this->createMock(GitClient::class);
        $gitClientMock
            ->expects(self::once())
            ->method('getLastCommitMessage')
            ->willReturn($expectedLastCommitMessage);

        $lastCommitMessageProvider = new CommitMessageProvider($gitClientMock);

        self::assertEquals($expectedLastCommitMessage, $lastCommitMessageProvider->getLastCommitMessage());
    }
}