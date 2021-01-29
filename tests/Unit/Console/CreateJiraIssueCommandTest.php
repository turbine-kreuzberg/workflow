<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Console\CreateJiraIssueCommand;
use Turbine\Workflow\Transfers\JiraIssueTransfer;
use Turbine\Workflow\Workflow\Jira\IssueCreator;
use Turbine\Workflow\Workflow\WorkflowFactory;

class CreateJiraIssueCommandTest extends TestCase
{
    public function testCreateImprovementTicket(): void
    {
        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('ask')
            ->with('Issue summary')
            ->willReturn('issue summary text');

        $issueCreatorMock = $this->createMock(IssueCreator::class);
        $jiraIssueTransfer = new JiraIssueTransfer();
        $jiraIssueTransfer->url = 'url';

        $issueCreatorMock->expects(self::once())
            ->method('createIssue')
            ->with('issue summary text', 'improvement')
            ->willReturn($jiraIssueTransfer);

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);

        $workflowFactoryMock->expects(self::once())
            ->method('createJiraIssueCreator')
            ->willReturn($issueCreatorMock);

        $createJiraIssueCommand = new CreateJiraIssueCommand(workflowFactory: $workflowFactoryMock, name: null);

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->expects(self::once())
            ->method('getArgument')
            ->with('issueType')
            ->willReturn('improvement');

        $inputMock->expects(self::once())
            ->method('getOption')
            ->with('forSprint')
            ->willReturn(null);

        $outputMock = $this->createMock(OutputInterface::class);

        $createJiraIssueCommand->run($inputMock, $outputMock);
    }

    public function testCreateTicketForSprint(): void
    {
        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('ask')
            ->with('Issue summary')
            ->willReturn('issue summary text');

        $issueCreatorMock = $this->createMock(IssueCreator::class);
        $jiraIssueTransfer = new JiraIssueTransfer();
        $jiraIssueTransfer->url = 'url';

        $issueCreatorMock->expects(self::never())
            ->method('createIssue');

        $issueCreatorMock->expects(self::once())
            ->method('createIssueForSprint')
            ->with('issue summary text', 'improvement')
            ->willReturn($jiraIssueTransfer);

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);

        $workflowFactoryMock->expects(self::once())
            ->method('createJiraIssueCreator')
            ->willReturn($issueCreatorMock);

        $createJiraIssueCommand = new CreateJiraIssueCommand(workflowFactory: $workflowFactoryMock, name: null);

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->expects(self::once())
            ->method('getArgument')
            ->with('issueType')
            ->willReturn('improvement');

        $inputMock->expects(self::once())
            ->method('getOption')
            ->with('forSprint')
            ->willReturn(true);

        $outputMock = $this->createMock(OutputInterface::class);

        $createJiraIssueCommand->run($inputMock, $outputMock);
    }

    public function testCreateIssueThrowExceptionWithInvalidIssueType(): void
    {
        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('ask')
            ->with('Issue summary')
            ->willReturn('issue summary text');

        $issueCreatorMock = $this->createMock(IssueCreator::class);
        $jiraIssueTransfer = new JiraIssueTransfer();
        $jiraIssueTransfer->url = 'url';

        $issueCreatorMock->expects(self::never())
            ->method('createIssue');

        $issueCreatorMock->expects(self::never())
            ->method('createIssueForSprint');

        $workflowFactoryMock = $this->createMock(WorkflowFactory::class);
        $workflowFactoryMock->expects(self::once())
            ->method('createSymfonyStyle')
            ->willReturn($symfonyStyleMock);

        $workflowFactoryMock->expects(self::never())
            ->method('createJiraIssueCreator');

        $createJiraIssueCommand = new CreateJiraIssueCommand(workflowFactory: $workflowFactoryMock, name: null);

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->expects(self::once())
            ->method('getArgument')
            ->with('issueType')
            ->willReturn(null);

        $inputMock->expects(self::never())
            ->method('getOption');

        $outputMock = $this->createMock(OutputInterface::class);

        $this->expectException(RuntimeException::class);
        $createJiraIssueCommand->run($inputMock, $outputMock);
    }
}
