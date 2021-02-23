<?php

namespace Turbine\Workflow\Console;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\WorkflowFactory;

class MoveJiraIssueCommand extends Command
{
    private const ARGUMENT_TICKET_NUMBER = 'ticket number';

    /**
     * @var string
     */
    protected static $defaultName = 'workflow:move:jira-issue';

    public function __construct(
        private WorkflowFactory $workflowFactory,
        private IssueUpdater $issueUpdater
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Transition the status of a jira issue.');
        $this->addArgument(
            self::ARGUMENT_TICKET_NUMBER,
            InputArgument::OPTIONAL,
            'You can provide the ticket number directly'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutputStyle = $this->workflowFactory->createSymfonyStyle($input, $output);

        $issueNumber = $this->getIssueNumber($input, $inputOutputStyle);

        $choices = $this->workflowFactory->createTicketTransitionStatusChoicesProvider()->provide($issueNumber);

        if (empty($choices)) {
            $inputOutputStyle->writeln('There are no transitions possible for this ticket.');

            return Command::SUCCESS;
        }

        $choice = $inputOutputStyle->choice(
            'Move ticket to',
            array_values($choices)
        );

        try {
            $this->issueUpdater->moveIssueToStatus($issueNumber, $choice);
        } catch (Exception $exception) {
            $inputOutputStyle->error(
                sprintf('An error occurred moving ticket "%s" to status "%s"', $issueNumber, $choice)
            );

            return Command::SUCCESS;
        }

        $inputOutputStyle->success('Ticket moved successfully to status ' . $choice);

        $shouldAssign = $inputOutputStyle->confirm('Assign yourself to the ticket?');

        if ($shouldAssign) {
            $this->issueUpdater->assignJiraIssueToUser($issueNumber);
        }

        $inputOutputStyle->success('Bye!!');

        return Command::SUCCESS;
    }

    private function getIssueNumber(InputInterface $input, SymfonyStyle $inputOutputStyle): string
    {
        $argumentTicketNumber = $input->getArgument(self::ARGUMENT_TICKET_NUMBER);

        return $argumentTicketNumber ?: $inputOutputStyle->ask('Ticket number');
    }
}
