<?php
declare(strict_types = 1);


namespace Unit\Workflow\Provider;

use PHPUnit\Framework\TestCase;
use Workflow\Configuration;
use Workflow\Workflow\Exception\MalformedCommitMessageException;
use Workflow\Workflow\Provider\CommitMessageProvider;
use Workflow\Workflow\Provider\FastWorklogProvider;

class FastWorklogProviderTest extends TestCase
{

    /**
     * @dataProvider provideMalformedCommitMessages
     */
    public function testFastWorklogFail(string $commitMessage) : void
    {
        $commitMessageProvider = $this
            ->createMock(CommitMessageProvider::class);
        $commitMessageProvider
            ->method('getLastCommitMessage')
            ->willReturn($commitMessage);

        $fastWorklogProvider = new FastWorklogProvider(
            $commitMessageProvider,
            new Configuration()
        );
        self::expectException(MalformedCommitMessageException::class);
        $fastWorklogProvider->provide();

    }

    public function provideMalformedCommitMessages(): array
    {
        return [
            ['BCM -999 i am not well formed'],
            ['999 i am not well formed'],
        ];
    }

    /**
     * @dataProvider provideCommitMessages
     */
    public function testFastWorklog(string $commitMessage, string $expectedIssueNumber, string $expectedMessage) : void
    {
        $commitMessageProvider = $this
            ->createMock(CommitMessageProvider::class);
        $commitMessageProvider
            ->method('getLastCommitMessage')
            ->willReturn($commitMessage);

        $fastWorklogProvider = new FastWorklogProvider(
            $commitMessageProvider,
            new Configuration()
        );
        [$issueNumber, $message] = $fastWorklogProvider->provide();
        self::assertEquals($expectedIssueNumber, $issueNumber);
        self::assertEquals($expectedMessage, $message);

    }

    public function provideCommitMessages(): array
    {
        return [
            ['BCM-123 add test', 'BCM-123', 'add test'],
            ['BCM-999 add even more tests', 'BCM-999', 'add even more tests'],
        ];
    }


}