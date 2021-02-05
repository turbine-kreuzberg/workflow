<?php

namespace Turbine\Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Transfers\JiraIssueTransfer;
use Turbine\Workflow\Workflow\WorkflowFactory;

class GetJiraIssueDataCommand extends Command
{
    private const ARGUMENT_TICKET_NUMBER = 'ticket number';

    /**
     * @var string
     */
    protected static $defaultName = 'workflow:get:jira-issue';

    /**
     * @param \Turbine\Workflow\Workflow\WorkflowFactory $workflowFactory
     * @param \Turbine\Workflow\Configuration $configuration
     */
    public function __construct(
        private WorkflowFactory $workflowFactory,
        private Configuration $configuration
    ) {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Get data from a jira issue.');
        $this->addArgument(
            self::ARGUMENT_TICKET_NUMBER,
            InputArgument::OPTIONAL,
            'You can provide the ticket number directly'
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutputStyle = $this->workflowFactory->createSymfonyStyle($input, $output);

        $issueData = null;

        while ($issueData === null) {
            $issueNumber = $input->getArgument(
                self::ARGUMENT_TICKET_NUMBER
            ) ?: $inputOutputStyle->ask('Ticket number (x to exit)');

            if ($issueNumber === 'x') {
                $inputOutputStyle->success('Bye!');
                return Command::SUCCESS;
            }

            try {
                $issueData = $this->getJiraIssue($issueNumber);
            } catch (\Throwable $exception) {
                $inputOutputStyle->error('Did you write the right ticket number? Try again!');
            }
        }

        if ($issueData === null) {
            return Command::FAILURE;
        }

        $inputOutputStyle->title(
            sprintf('<options=bold>%s</> - %s', $issueData->key, $issueData->summary)
        );

        $this->outputIssueType($issueData, $inputOutputStyle);

        if ($issueData->description) {
            $inputOutputStyle->section('Description');
            $inputOutputStyle->text($issueData->description);
        }

        $this->outputInformationBlock($inputOutputStyle, $issueData);

        $this->outputSubTasks($inputOutputStyle, $issueData);

        $inputOutputStyle
            ->writeln('<href=' . $issueData->url . '>\<Click here to open in browser></>');

        $inputOutputStyle->newLine();

        return Command::SUCCESS;
    }

    /**
     * @param string $issueNumber
     *
     * @return \Turbine\Workflow\Transfers\JiraIssueTransfer
     */
    private function getJiraIssue(string $issueNumber): JiraIssueTransfer
    {
        $issueKey = $issueNumber;

        if (is_numeric($issueNumber)) {
            $issueKey = $this->configuration->getConfiguration(Configuration::JIRA_PROJECT_KEY) . '-' . $issueNumber;
        }

        return $this->workflowFactory->createJiraIssueReader()->getIssue($issueKey);
    }

    /**
     * @param \Turbine\Workflow\Transfers\JiraIssueTransfer $issueData
     * @param \Symfony\Component\Console\Style\SymfonyStyle $inputOutputStyle
     *
     * @return void
     */
    private function outputIssueType(JiraIssueTransfer $issueData, SymfonyStyle $inputOutputStyle): void
    {
        $ticketType = $this->getFormattedTicketType($issueData->type);

        if ($issueData->isSubTask) {
            $ticketType .= sprintf(
                ' of %s %s - %s',
                $this->getFormattedTicketType($issueData->parentIssueType),
                $issueData->parentIssueKey,
                $issueData->parentIssueSummary
            );
        }
        $inputOutputStyle->section('Type  ' . $ticketType);
    }

    /**
     * @param string|null $type
     *
     * @return string|null
     */
    private function getFormattedTicketType(?string $type): ?string
    {
        if ($type === null) {
            return null;
        }

        switch (strtolower($type)) {
        case 'bug':
            $color = 'red';
            break;
        case 'sub-task':
            $color = 'cyan';
            break;
        case 'task':
            $color = 'blue';
            break;
        case 'story':
            $color = 'green';
            break;
        default:
            $color = 'white';
        }

        return sprintf('<fg=%s>%s</>', $color, $type);
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $inputOutputStyle
     * @param \Turbine\Workflow\Transfers\JiraIssueTransfer $issueData
     *
     * @return void
     */
    private function outputInformationBlock(SymfonyStyle $inputOutputStyle, JiraIssueTransfer $issueData): void
    {
        $inputOutputStyle->section('Information');
        $inputOutputStyle->definitionList(
            ['Status' => $issueData->currentStatus],
            ['Created at' => $issueData->createdAt],
            ['Assignee' => $issueData->assignee],
            ['Time spent' => $issueData->aggregateTimeSpent ?? $issueData->timeSpent],
        );
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $inputOutputStyle
     * @param \Turbine\Workflow\Transfers\JiraIssueTransfer $issueData
     *
     * @return void
     */
    private function outputSubTasks(SymfonyStyle $inputOutputStyle, JiraIssueTransfer $issueData): void
    {
        if (count($issueData->subTasks) === 0) {
            return;
        }

        $subtasks = [];
        foreach ($issueData->subTasks as $subTask) {
            $subtasks[] = sprintf(
                '%s - %s (%s)',
                $subTask['key'],
                $subTask['fields']['summary'],
                $subTask['fields']['status']['name']
            );
        }

        $inputOutputStyle->section('Sub-tasks');
        $inputOutputStyle->listing($subtasks);
    }
}
