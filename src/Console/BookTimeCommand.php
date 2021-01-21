<?php

namespace Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Workflow\Configuration;
use Workflow\Exception\JiraNoWorklogException;
use Workflow\Transfers\JiraWorklogEntryTransfer;
use Workflow\Workflow\Exception\MalformedCommitMessageException;
use Workflow\Workflow\WorkflowFactory;

class BookTimeCommand extends Command
{
    private const COMMAND_NAME = 'workflow:book-time';
    private const FOR_CURRENT_BRANCH = 'forCurrentBranch';
    private const CUSTOM_INPUT_KEY = 'custom';
    private const CUSTOM_INPUT = 'Custom input';
    private const FAST_WORKLOG = 'fast-worklog';

    private WorkflowFactory $workflowFactory;

    public function __construct(?string $name = null, private Configuration $configuration)
    {
        parent::__construct($name);
        $this->workflowFactory = new WorkflowFactory();
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Book time for a jira issue.');
        $this->addOption(
            self::FOR_CURRENT_BRANCH,
            null,
            InputOption::VALUE_NONE,
            'Use this option to book time for current branch'
        );
        $this->addOption(
            self::FAST_WORKLOG,
            null,
            InputOption::VALUE_NONE,
            'Use this option to enable fast worklog'
        );
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutputStyle = new SymfonyStyle($input, $output);
        $today = date('Y-m-d') . 'T12:00:00.000+0000';

        [$issue, $worklogComment] = $this->workflowFactory->createFastWorklogProvider()->provide();

        if (isset($issue, $worklogComment)
            && (bool)$input->getOption(self::FAST_WORKLOG) === true
        ) {
            $questionFastWorklog = sprintf(
                'How much time do you want to book on <fg=yellow>[%s]</> with message <fg=yellow>"%s"</>',
                $issue,
                $worklogComment
            );
            $duration = $inputOutputStyle->ask($questionFastWorklog);
            $bookedTimeInMinutes = $this
                ->workflowFactory
                ->createJiraIssueUpdater()
                ->bookTime($issue, $worklogComment, $duration, $today);
            $inputOutputStyle->success(
                'Booked '
                . $bookedTimeInMinutes
                . ' minutes for "'
                . $worklogComment
                . '" on '
                . $issue
            );

            return 0;
        }

        try {
            $issue = $this->getIssueTicketNumber($input, $inputOutputStyle);
            $worklogComment = $this->createWorklogComment($issue, $inputOutputStyle);
            $lastTicketWorklog = $this->workflowFactory->createJiraIssueReader()->getLastTicketWorklog($issue);
            $duration = $this->createWorklogDuration($lastTicketWorklog, $inputOutputStyle);
        } catch (JiraNoWorklogException $jiraNoWorklogException) {
            $worklogComment = $inputOutputStyle->ask('What did you do');
            $duration = $inputOutputStyle->ask('For how long did you do it');
        }

        $bookedTimeInMinutes = $this->workflowFactory->createJiraIssueUpdater()->bookTime(
            $issue,
            $worklogComment,
            $duration,
            $today
        );

        $inputOutputStyle->success(
            'Booked '
            . $bookedTimeInMinutes
            . ' minutes for "'
            . $worklogComment
            . '" on '
            . $issue
        );

        return 0;
    }

    private function createWorklogComment(
        string $issueNumber,
        SymfonyStyle $inputOutputStyle
    ): string {
        $worklogChoices = $this->workflowFactory->createWorklogChoiceProvider()->provide($issueNumber);

        $worklogChoices[] = self::CUSTOM_INPUT;

        $commentChoice = $inputOutputStyle->choice(
            'Choose your worklog comment',
            $worklogChoices,
            $worklogChoices[0]
        );

        $summary = $commentChoice;
        if ($commentChoice === self::CUSTOM_INPUT) {
            $summary = $inputOutputStyle->ask('What did you do');
        }

        return $summary;
    }

    private function createWorklogDuration(
        JiraWorklogEntryTransfer $worklog,
        SymfonyStyle $inputOutputStyle
    ): float {
        $timeSpentInMinutes = $worklog->timeSpentSeconds / 60;

        return (float)$inputOutputStyle->ask('For how long did you do it', (string)$timeSpentInMinutes);
    }

    private function getIssueTicketNumber(InputInterface $input, SymfonyStyle $inputOutputStyle): string
    {
        if ($input->getOption(self::FOR_CURRENT_BRANCH)) {
            return $this->workflowFactory->getBookTime()->extractTicketIdFromCurrentBranch();
        }

        $choices = $this->workflowFactory->createFavouriteTicketChoicesProvider()->provide();

        if (empty($choices)) {
            return $inputOutputStyle->ask('What ticket do you want to book time on? Ticket number');
        }

        $choices[self::CUSTOM_INPUT_KEY] = self::CUSTOM_INPUT;

        $choice = $inputOutputStyle->choice(
            'What ticket do you want to book time on',
            $choices,
            self::CUSTOM_INPUT_KEY
        );

        if ($choice !== self::CUSTOM_INPUT_KEY) {
            return $choice;
        }

        return $inputOutputStyle->ask('What ticket do you want to book time on? Ticket number');
    }

}
