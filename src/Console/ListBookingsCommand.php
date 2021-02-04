<?php

namespace Turbine\Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Client\JiraClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Exception\JiraNoWorklogException;
use Turbine\Workflow\Transfers\JiraWorklogEntryTransfer;
use Turbine\Workflow\Workflow\WorkflowFactory;

class ListBookingsCommand extends Command
{
    private const COMMAND_NAME = 'workflow:list-bookings';

    private WorkflowFactory $workflowFactory;

    public function __construct(?string $name = null, private Configuration $configuration)
    {
        parent::__construct($name);
        $this->workflowFactory = new WorkflowFactory();
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('List bookings of the day.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutputStyle = new SymfonyStyle($input, $output);

        $formattedBookings = '';
        $completeWorklog = $this->workflowFactory->createJiraIssueReader()->getCompleteWorklog();
        foreach ($completeWorklog['tickets'] as $worklog) {
            $formattedBookings .= sprintf(
                "\n%s - %s (%s)",
                str_pad($worklog['number'], 8),
                $worklog['comment'],
                $worklog['bookedTime']
            );
        }

        $inputOutputStyle->success(
            'Daily Bookings: ' . $completeWorklog['totalBookedTime']
            . $formattedBookings
        );

        return 0;
    }
}
