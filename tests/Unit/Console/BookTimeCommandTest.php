<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Console\BookTimeCommand;
use Turbine\Workflow\Console\SubConsole\FastBookTimeConsole;
use Turbine\Workflow\Workflow\Jira\IssueReader;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\WorkflowFactory;

class BookTimeCommandTest extends TestCase
{
    public function testFastBookTime(): void
    {
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->expects(self::once())
            ->method('getOption')
            ->with('fast-worklog')
            ->willReturn(true);

        $outputMock = $this->createMock(OutputInterface::class);

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->with($inputMock, $outputMock)
            ->willReturn($symfonyStyleMock);

        $configurationMock = $this->createMock(Configuration::class);

        $fastBookTimeConsoleMock = $this->createMock(FastBookTimeConsole::class);
        $fastBookTimeConsoleMock->expects(self::once())
            ->method('execFastBooking')
            ->with($symfonyStyleMock, date('Y-m-d') . 'T12:00:00.000+0000')
            ->willReturn(true);

        $issueUpdaterMock = $this->createMock(IssueUpdater::class);
        $issueReaderMock = $this->createMock(IssueReader::class);

        $bookTimeCommand = new BookTimeCommand(
            $configurationMock,
            $workflowFactoryMock,
            $issueUpdaterMock,
            $issueReaderMock,
            $fastBookTimeConsoleMock
        );

        $bookTimeCommand->run($inputMock, $outputMock);
    }
}
