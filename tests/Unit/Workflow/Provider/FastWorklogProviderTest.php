<?php
declare(strict_types = 1);

namespace Unit\Workflow\Provider;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Workflow\Provider\CommitMessageProvider;
use Turbine\Workflow\Workflow\Provider\FastWorklogProvider;

class FastWorklogProviderTest extends TestCase
{
    /**
     * @dataProvider provideMalformedCommitMessages
     */
    public function testFastWorklogFail(string $commitMessage): void
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
        [$issue, $message] = $fastWorklogProvider->provide();
        self::assertNull($issue);
        self::assertNull($message);

    }

    public function provideMalformedCommitMessages(): array
    {
        return [
            ['commitMessage' => 'BCM -999 i am not well formed'],
            ['commitMessage' => '999 i am not well formed'],
        ];
    }

    /**
     * @dataProvider provideCommitMessages
     */
    public function testFastWorklog(string $commitMessage, string $expectedIssueNumber, string $expectedMessage): void
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
            [
                'commitMessage' => 'BCM-123 add test',
                'expectedIssueNumber' => 'BCM-123',
                'expectedMessage' => 'add test',
            ],
            [
                'commitMessage' => 'BCM-999 add even more tests',
                'expectedIssueNumber' => 'BCM-999',
                'expectedMessage' => 'add even more tests'
            ],
        ];
    }
}