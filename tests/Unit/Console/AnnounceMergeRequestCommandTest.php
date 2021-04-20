<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Console\AnnounceMergeRequestCommand;
use Turbine\Workflow\Console\MoveJiraIssueCommand;
use Turbine\Workflow\Exception\JiraStateNotFoundException;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\Model\MergeRequestAnnouncementBuilder;
use Turbine\Workflow\Workflow\Model\SlackMessageSender;
use Turbine\Workflow\Workflow\Provider\TicketTransitionStatusChoicesProvider;
use Turbine\Workflow\Workflow\WorkflowFactory;

class AnnounceMergeRequestCommandTest extends TestCase
{
    public function testAnnounceMergeRequest(): void
    {
        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock
            ->expects(self::once())
            ->method('success')
            ->with('Merge Request announcement was sent to slack channel');

        $mergeRequestAnnouncementBuilderMock = $this->createMock(MergeRequestAnnouncementBuilder::class);
        $mergeRequestAnnouncementBuilderMock
            ->expects(self::once())
            ->method('getAnnouncementMessageForSlack')
            ->willReturn('slack-message');

        $slackMessageSenderMock = $this->createMock(SlackMessageSender::class);
        $slackMessageSenderMock->expects(self::once())->method('send')->with('slack-message');

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);
        $workflowFactoryMock->expects(self::once())
            ->method('createSlackMessageSender')
            ->willReturn($slackMessageSenderMock);

        $announceMergeRequestCommand = new AnnounceMergeRequestCommand(
            workflowFactory: $workflowFactoryMock,
            mergeRequestAnnouncementBuilder: $mergeRequestAnnouncementBuilderMock
        );

        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $announceMergeRequestCommand->run($inputMock, $outputMock);
    }
}
