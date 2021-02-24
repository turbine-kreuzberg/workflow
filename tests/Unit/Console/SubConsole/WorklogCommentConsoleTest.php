<?php

namespace Unit\Console\SubConsole;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Console\SubConsole\WorklogCommentConsole;
use Turbine\Workflow\Workflow\Provider\WorklogChoicesProvider;

class WorklogCommentConsoleTest extends TestCase
{
    public function testWorklogCommentFromProvidedChoices(): void
    {
        $worklogChoicesProviderMock = $this->createMock(WorklogChoicesProvider::class);
        $worklogChoicesProviderMock->expects(self::once())
            ->method('provide')
            ->with('Ticket-134')
            ->willReturn(
                [
                    'provided worklog',
                    'last git commit',
                ]
            );

        $worklogCommentConsole = new WorklogCommentConsole($worklogChoicesProviderMock);

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('choice')
            ->with(
                'Choose your worklog comment',
                [
                    'provided worklog',
                    'last git commit',
                    'Custom input',
                ],
                'provided worklog'
            )
            ->willReturn('last git commit');

        self::assertEquals(
            'last git commit',
            $worklogCommentConsole->createWorklogComment('Ticket-134', $symfonyStyleMock)
        );
    }

    public function testWorklogCommentFromCustomInput(): void
    {
        $worklogChoicesProviderMock = $this->createMock(WorklogChoicesProvider::class);
        $worklogChoicesProviderMock->expects(self::once())
            ->method('provide')
            ->with('Ticket-134')
            ->willReturn(
                [
                    'provided worklog',
                    'last git commit',
                ]
            );

        $worklogCommentConsole = new WorklogCommentConsole($worklogChoicesProviderMock);

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('choice')
            ->with(
                'Choose your worklog comment',
                [
                    'provided worklog',
                    'last git commit',
                    'Custom input',
                ],
                'provided worklog'
            )
            ->willReturn('Custom input');

        $symfonyStyleMock->expects(self::once())
            ->method('ask')
            ->with('What did you do')
            ->willReturn('custom worklog comment');

        self::assertEquals(
            'custom worklog comment',
            $worklogCommentConsole->createWorklogComment('Ticket-134', $symfonyStyleMock)
        );
    }
}
