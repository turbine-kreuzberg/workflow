<?php

namespace Turbine\Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Console\SubConsole\FastBookTimeConsole;
use Turbine\Workflow\Console\SubConsole\TicketNumberConsole;
use Turbine\Workflow\Console\SubConsole\WorklogCommentConsole;
use Turbine\Workflow\Exception\JiraNoWorklogException;
use Turbine\Workflow\Transfers\JiraWorklogEntryTransfer;
use Turbine\Workflow\Workflow\Jira\IssueReader;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\TicketIdProvider;
use Turbine\Workflow\Workflow\WorkflowFactory;

class BookTimeCommand extends Command
{
    private const COMMAND_NAME = 'workflow:book-time';
    private const ARGUMENT_TICKET_NUMBER = 'ticket number';
    private const FOR_CURRENT_BRANCH = 'forCurrentBranch';
    private const FAST_WORKLOG = 'fast-worklog';

    public function __construct(
        private Configuration $configuration,
        private WorkflowFactory $workflowFactory,
        private IssueUpdater $issueUpdater,
        private IssueReader $issueReader,
        private FastBookTimeConsole $fastBookTimeConsole,
        private TicketIdProvider $ticketIdProvider,
        private TicketNumberConsole $ticketNumberConsole,
        private WorklogCommentConsole $worklogCommentConsole
    ) {
        parent::__construct();
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
        $this->addArgument(
            self::ARGUMENT_TICKET_NUMBER,
            InputArgument::OPTIONAL,
            'You can provide the ticket number directly'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutputStyle = $this->workflowFactory->createSymfonyStyle($input, $output);
        $today = date('Y-m-d') . 'T12:00:00.000+0000';

        if ((bool)$input->getOption(self::FAST_WORKLOG)) {
            if ($this->fastBookTimeConsole->execFastBooking($inputOutputStyle, $today)) {
                return 0;
            }
        }

        $issueNumber = $this->getIssueTicketNumber($input, $inputOutputStyle);

        $issue = $this->issueReader->getIssue($issueNumber);
        $inputOutputStyle->title(\sprintf('Book time on ticket: %s - %s', $issue->key, $issue->summary));

        $worklogComment = $this->worklogCommentConsole->createWorklogComment($issueNumber, $inputOutputStyle);

        try {
            $lastTicketWorklog = $this->issueReader->getLastTicketWorklog($issueNumber);
            $duration = $this->createWorklogDuration($lastTicketWorklog, $inputOutputStyle);
        } catch (JiraNoWorklogException $jiraNoWorklogException) {
            $duration = $inputOutputStyle->ask('For how long did you do it');
        }

        $bookedTimeInMinutes = $this->issueUpdater->bookTime(
            $issueNumber,
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
            . $issueNumber
            . "\nTotal booked time today: "
            . $this->issueReader->getTimeSpentToday()
            . 'h'
        );

        return 0;
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
            return $this->ticketIdProvider->extractTicketIdFromCurrentBranch();
        }

        $argumentTicketNumber = $input->getArgument(self::ARGUMENT_TICKET_NUMBER);
        if ($argumentTicketNumber !== null && is_string($argumentTicketNumber)) {
            return $argumentTicketNumber;
        }

        return $this->ticketNumberConsole->getIssueTicketNumber($inputOutputStyle);
    }

}
