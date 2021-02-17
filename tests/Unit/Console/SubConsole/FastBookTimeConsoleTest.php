<?php

namespace Unit\Console\SubConsole;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Console\SubConsole\FastBookTimeConsole;
use Turbine\Workflow\Workflow\Jira\IssueReader;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\Provider\FastWorklogProvider;

class FastBookTimeConsoleTest extends TestCase
{
    public function testFastBookTimeSuccessful(): void
    {
        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('ask')
            ->with('How much time do you want to book on <fg=yellow>[123]</> with message <fg=yellow>"message"</>')
            ->willReturn(3600);

        $symfonyStyleMock->expects(self::once())
            ->method('success')
            ->with("Booked 3600 minutes for \"message\" on 123\nTotal booked time today: 8.5h");

        $issueUpdaterMock = $this->createMock(IssueUpdater::class);
        $issueUpdaterMock->expects(self::once())
            ->method('bookTime')
            ->with('123', 'message', 3600.0, date('Y-m-d') . 'T12:00:00.000+0000')
            ->willReturn(3600.0);

        $fastWorklogProviderMock = $this->createMock(FastWorklogProvider::class);
        $fastWorklogProviderMock->expects(self::once())
            ->method('provide')
            ->willReturn(['123', 'message']);

        $issueReaderMock = $this->createMock(IssueReader::class);
        $issueReaderMock->expects(self::once())
            ->method('getTimeSpentToday')
            ->willReturn(8.5);

        $fastBookTimeConsole = new FastBookTimeConsole(
            $fastWorklogProviderMock,
            $issueUpdaterMock,
            $issueReaderMock
        );

        self::assertTrue(
            $fastBookTimeConsole->execFastBooking(
                $symfonyStyleMock,
                date('Y-m-d') . 'T12:00:00.000+0000'
            )
        );
    }

    public function testFastBookTimeFailsBecauseOfInvalidProvidedWorklog(): void
    {
        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $issueUpdaterMock = $this->createMock(IssueUpdater::class);

        $fastWorklogProviderMock = $this->createMock(FastWorklogProvider::class);
        $fastWorklogProviderMock->expects(self::once())
            ->method('provide')
            ->willReturn([null, null]);

        $issueReaderMock = $this->createMock(IssueReader::class);

        $fastBookTimeConsole = new FastBookTimeConsole(
            $fastWorklogProviderMock,
            $issueUpdaterMock,
            $issueReaderMock
        );

        self::assertFalse(
            $fastBookTimeConsole->execFastBooking(
                $symfonyStyleMock,
                date('Y-m-d') . 'T12:00:00.000+0000'
            )
        );
    }
}
