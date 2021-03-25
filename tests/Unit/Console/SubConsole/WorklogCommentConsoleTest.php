<?php

namespace Unit\Console\SubConsole;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Console\SubConsole\WorklogCommentConsole;
use Turbine\Workflow\Workflow\Provider\FavouriteWorklogCommentChoicesProvider;
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
                    'test-comment',
                ]
            );

        $favouriteWorklogCommentChoicesProviderMock = $this->createMock(FavouriteWorklogCommentChoicesProvider::class);
        $favouriteWorklogCommentChoicesProviderMock
            ->expects($this->once())
            ->method('provide')
            ->willReturn(['another-comment', 'test-comment']);

        $worklogCommentConsole = new WorklogCommentConsole(
            $worklogChoicesProviderMock,
            $favouriteWorklogCommentChoicesProviderMock
        );

        $expectedChoices = [
            'provided worklog',
            'last git commit',
            'test-comment',
            'another-comment',
            'Custom input',
        ];
        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('choice')
            ->with(
                'Choose your worklog comment',
                $expectedChoices,
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

        $favouriteWorklogCommentChoicesProviderMock = $this->createMock(FavouriteWorklogCommentChoicesProvider::class);
        $favouriteWorklogCommentChoicesProviderMock
            ->expects($this->once())
            ->method('provide')
            ->willReturn([]);

        $worklogCommentConsole = new WorklogCommentConsole(
            $worklogChoicesProviderMock,
            $favouriteWorklogCommentChoicesProviderMock
        );

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
