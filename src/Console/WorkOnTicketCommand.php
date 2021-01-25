<?php

namespace Workflow\Console;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Workflow\Workflow\WorkflowFactory;

class WorkOnTicketCommand extends Command
{
    public const COMMAND_NAME = 'workflow:work-on-ticket';

    public function __construct(?string $name = null, private WorkflowFactory $workflowFactory)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription(
            'creates a new feature branch for the given ticket and assigns the task to the developer'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutputStyle = new SymfonyStyle($input, $output);

        $workOnTicket = $this->workflowFactory->createWorkOnTicket();

        $ticketNumber = $inputOutputStyle->ask('Ticket number?');
        $branchNameFromTicketCutAtFifty = substr($workOnTicket->getBranchNameFromTicket($ticketNumber), 0, 50);

        $question = (new Question(
            "Branch name?\n   $branchNameFromTicketCutAtFifty\n   " . str_repeat('-', 50) . "",
            $branchNameFromTicketCutAtFifty
        )
        )
            ->setAutocompleterValues([$branchNameFromTicketCutAtFifty])
            ->setValidator(
                function ($name) {
                    return $this->validateInputBranchName($name);
                }
            );
        $branchName = $inputOutputStyle->askQuestion($question);

        $workOnTicket->workOnTicket($ticketNumber, $branchName);

        $inputOutputStyle->success(
            [
                'Created new branch: ' . $branchName,
                'Moved ticket to in progress.',
            ]
        );

        return 0;
    }

    private function validateInputBranchName(string $branchName): string
    {
        if (!preg_match('/^[a-z0-9-]+$/i', $branchName)) {
            throw new RuntimeException(
                'Invalid branch name (permitted are only lower case characters, numbers and the dash).'
            );
        }

        if (strlen($branchName) > 50) {
            throw new RuntimeException('Invalid branch name (maximal 50 characters).');
        }

        return $branchName;
    }
}