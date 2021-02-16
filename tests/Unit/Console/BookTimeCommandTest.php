<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Console\BookTimeCommand;
use Turbine\Workflow\Workflow\Jira\IssueReader;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\Provider\FastWorklogProvider;
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
        $symfonyStyleMock->expects(self::once())
            ->method('ask')
            ->with('How much time do you want to book on <fg=yellow>[123]</> with message <fg=yellow>"message"</>')
            ->willReturn(3600);

        $symfonyStyleMock->expects(self::once())
            ->method('success')
            ->with("Booked 3600 minutes for \"message\" on 123\nTotal booked time today: 8.5h");

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->with($inputMock, $outputMock)
            ->willReturn($symfonyStyleMock);

        $configurationMock = $this->createMock(Configuration::class);

        $fastWorklogProviderMock = $this->createMock(FastWorklogProvider::class);
        $fastWorklogProviderMock->expects(self::once())
            ->method('provide')
            ->willReturn(['123', 'message']);

        $issueUpdaterMock = $this->createMock(IssueUpdater::class);
        $issueUpdaterMock->expects(self::once())
            ->method('bookTime')
            ->with('123', 'message', 3600.0, date('Y-m-d') . 'T12:00:00.000+0000')
            ->willReturn(3600.0);

        $issueReaderMock = $this->createMock(IssueReader::class);
        $issueReaderMock->expects(self::once())
            ->method('getTimeSpentToday')
            ->willReturn(8.5);

        $bookTimeCommand = new BookTimeCommand(
            $configurationMock,
            $workflowFactoryMock,
            $fastWorklogProviderMock,
            $issueUpdaterMock,
            $issueReaderMock
        );

        $bookTimeCommand->run($inputMock, $outputMock);
    }
}
