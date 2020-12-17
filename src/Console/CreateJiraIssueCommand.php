<?php

namespace Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Workflow\Transfers\JiraIssueTransfer;
use Workflow\Workflow\WorkflowFactory;

class CreateJiraIssueCommand extends Command
{
    private const COMMAND_NAME = 'workflow:create:jira-issue';
    private const FOR_SPRINT_OPTION = 'forSprint';
    private const ISSUE_TYPE_ATTRIBUTE = 'issueType';

    public function __construct(?string $name = null, private WorkflowFactory $workflowFactory)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Create a jira issue.');
        $this->addOption(
            self::FOR_SPRINT_OPTION,
            null,
            InputOption::VALUE_NONE,
            'Use this option to add the issued to current sprint'
        );

        $this->addArgument(
            self::ISSUE_TYPE_ATTRIBUTE,
            InputArgument::REQUIRED,
            'Set the type of an issue (e.g. bug, improvement)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $inputOutputStyle = new SymfonyStyle($input, $output);
        $summary = $inputOutputStyle->ask('Issue summary');
        $issue = $this->createIssue($input, $summary);

        $inputOutputStyle->success('Created issue: ' . $issue->url);

        return 0;
    }

    private function createIssue(InputInterface $input, string $summary): JiraIssueTransfer
    {
        $issueType = $input->getArgument(self::ISSUE_TYPE_ATTRIBUTE);

        if (\is_array($issueType) || $issueType === null) {
            throw new \RuntimeException('Invalid issue type option.');
        }

        if ($input->getOption(self::FOR_SPRINT_OPTION)) {
            return $this->workflowFactory->createJiraIssueCreator()->createIssueForSprint($summary, $issueType);
        }

        return $this->workflowFactory->createJiraIssueCreator()->createIssue($summary, $issueType);
    }
}
