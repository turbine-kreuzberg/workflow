<?php
declare(strict_types = 1);


namespace Unit\Workflow\Provider;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Client\GitClient;
use Turbine\Workflow\Workflow\Provider\CommitMessageProvider;

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

    public function testCommitMessageWithoutHotfixIsNoHotfixCommitMessage() : void
    {
        $expectedLastCommitMessage = 'git it from git';
        $gitClientMock = $this->createMock(GitClient::class);
        $gitClientMock
            ->expects(self::once())
            ->method('getLastCommitMessage')
            ->willReturn($expectedLastCommitMessage);

        $lastCommitMessageProvider = new CommitMessageProvider($gitClientMock);

        self::assertFalse($lastCommitMessageProvider->isHotfixCommitMessage());
    }

    public function testCommitMessageWithHotfixIsHotfixCommitMessage() : void
    {
        $expectedLastCommitMessage = 'git hOTfix from git';
        $gitClientMock = $this->createMock(GitClient::class);
        $gitClientMock
            ->expects(self::once())
            ->method('getLastCommitMessage')
            ->willReturn($expectedLastCommitMessage);

        $lastCommitMessageProvider = new CommitMessageProvider($gitClientMock);

        self::assertTrue($lastCommitMessageProvider->isHotfixCommitMessage());
    }
}
