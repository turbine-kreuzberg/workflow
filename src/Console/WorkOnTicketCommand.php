<?php

namespace Turbine\Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Workflow\Validator\BranchNameValidator;
use Turbine\Workflow\Workflow\WorkflowFactory;

class WorkOnTicketCommand extends Command
{
    public const COMMAND_NAME = 'workflow:work-on-ticket';

    public function __construct(
        private WorkflowFactory $workflowFactory,
        private BranchNameValidator $branchNameValidator
    ) {
        parent::__construct();
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
                function (string $name): string {
                    return $this->branchNameValidator->validate($name);
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
}