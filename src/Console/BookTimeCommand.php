<?php

namespace Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Workflow\Client\ClientFactory;
use Workflow\Exception\JiraNoWorklogException;
use Workflow\Transfers\JiraWorklogEntryTransfer;
use Workflow\Workflow\WorkflowFactory;

class BookTimeCommand extends Command
{
    private const COMMAND_NAME = 'workflow:book-time';
    private const FOR_CURRENT_BRANCH = 'forCurrentBranch';
    private const CUSTOM_INPUT_KEY = 'custom';
    private const CUSTOM_INPUT = 'Custom input';
    private const JIRA_FAVOURITE_TICKETS = 'JIRA_FAVOURITE_TICKETS';

    private WorkflowFactory $workflowFactory;

    private ClientFactory $clientFactory;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->workflowFactory = new WorkflowFactory();
        $this->clientFactory = new ClientFactory();
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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutputStyle = new SymfonyStyle($input, $output);

        $issue = $this->getIssueTicketNumber($input, $inputOutputStyle);

        try {
            $worklog = $this->workflowFactory->createJiraIssueReader()->getLastTicketWorklog($issue);
            $worklogComment = $this->createWorklogComment($worklog, $inputOutputStyle);
            $duration = $this->createWorklogDuration($worklog, $inputOutputStyle);
        } catch (JiraNoWorklogException $jiraNoWorklogException) {
            $worklogComment = $inputOutputStyle->ask('What did you do');
            $duration = $inputOutputStyle->ask('For how long did you do it');
        }

        $today = date('Y-m-d') . 'T12:00:00.000+0000';

        if ($duration < 15) {
            $duration *= 60;
        }
        $this->workflowFactory->createJiraIssueUpdater()->bookTime($issue, $worklogComment, $duration, $today);

        $inputOutputStyle->success('Booked ' . $duration . ' minutes for "' . $worklogComment . '" on ' . $issue);
    }

    private function createWorklogComment(
        JiraWorklogEntryTransfer $worklog,
        SymfonyStyle $inputOutputStyle
    ): string {
        $worklogComment = $worklog->comment . ' (from ' . $worklog->author . ')';
        $commentChoice = $inputOutputStyle->choice(
            'Choose your worklog comment',
            [$worklogComment, self::CUSTOM_INPUT],
            $worklogComment
        );

        $summary = $worklog->comment;
        if ($commentChoice === self::CUSTOM_INPUT) {
            $summary = $inputOutputStyle->ask('What did you do');
        }

        return $summary;
    }

    private function createWorklogDuration(
        JiraWorklogEntryTransfer $worklog,
        SymfonyStyle $inputOutputStyle
    ): int {
        $timeSpentInMinutes = $worklog->timeSpentSeconds / 60;
        $durationChoice = $inputOutputStyle->choice(
            'Choose your time spent (in minutes)',
            [$timeSpentInMinutes, self::CUSTOM_INPUT],
            $timeSpentInMinutes
        );

        $duration = $durationChoice;
        if ($durationChoice === self::CUSTOM_INPUT) {
            $duration = (int)$inputOutputStyle->ask('For how long did you do it');
        }

        return $duration;
    }

    private function getIssueTicketNumber(InputInterface $input, SymfonyStyle $inputOutputStyle): string
    {
        if ($input->getOption(self::FOR_CURRENT_BRANCH)) {
            return $this->clientFactory->getGitClient()->getTicketFromCurrentBranch();
        }

        $favouriteTicketsFromEnvironment = $this->getFavouriteTicketsFromEnvironment();

        if (empty($favouriteTicketsFromEnvironment)) {
            return  $inputOutputStyle->ask('What ticket do you want to book time on? Ticket number');
        }

        $choices = [];
        $choices = $this->addFavouriteTicketsToChoices($favouriteTicketsFromEnvironment, $choices);
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

    private function getFavouriteTicketsFromEnvironment(): string
    {
        $envVarname = self::JIRA_FAVOURITE_TICKETS;
        if (getenv($envVarname)) {
            return getenv($envVarname);
        }

        return '';
    }

    private function addFavouriteTicketsToChoices(string $favouriteTicketsFromEnvironment, array $choices): array
    {
        $issueArray = explode(',', $favouriteTicketsFromEnvironment);

        $favouriteIssues = $this->workflowFactory->createJiraIssueReader()->getIssues($issueArray);

        foreach ($favouriteIssues as $issue) {
            $choices[$issue->key] = $issue->summary;
        }

        return $choices;
    }
}
