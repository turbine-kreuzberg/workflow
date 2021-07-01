<?php

declare(strict_types=1);

namespace Turbine\Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Turbine\Workflow\Workflow\Jira\IssueReader;
use Turbine\Workflow\Workflow\WorkflowFactory;

class ListBookingsCommand extends Command
{
    private const COMMAND_NAME = 'workflow:list-bookings';

    public function __construct(
        private IssueReader $jiraIssueReader,
        private WorkflowFactory $workflowFactory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('List bookings of the day.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutputStyle = $this->workflowFactory->createSymfonyStyle($input, $output);

        $formattedBookings = '';
        $completeWorklog = $this->jiraIssueReader->getCompleteWorklog();
        foreach ($completeWorklog->jiraWorklogEntryCollection as $jiraWorklogEntryTransfer) {
            $formattedBookings .= sprintf(
                "\n%s - %s (%s)",
                str_pad($jiraWorklogEntryTransfer->key, 8),
                $jiraWorklogEntryTransfer->comment,
                gmdate('H:i', $jiraWorklogEntryTransfer->timeSpentSeconds)
            );
        }

        $inputOutputStyle->success(
            'Daily Bookings: ' . gmdate('H:i', $completeWorklog->totalSpentTime)
            . $formattedBookings
        );

        return 0;
    }
}
