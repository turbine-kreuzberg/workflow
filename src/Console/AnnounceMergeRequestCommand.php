<?php

declare(strict_types=1);

namespace Turbine\Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Turbine\Workflow\Workflow\Model\MergeRequestAnnouncementBuilder;
use Turbine\Workflow\Workflow\WorkflowFactory;

class AnnounceMergeRequestCommand extends Command
{
    private const COMMAND_NAME = 'merge-request:announce';

    public function __construct(
        private WorkflowFactory $workflowFactory,
        private MergeRequestAnnouncementBuilder $mergeRequestAnnouncementBuilder
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Announces merge request on slack');
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $inputOutputStyle = $this->workflowFactory->createSymfonyStyle($input, $output);

        $message = $this->mergeRequestAnnouncementBuilder->getAnnouncementMessageForSlack();
        $this->workflowFactory->createSlackMessageSender()->send($message);

        $inputOutputStyle->success('Merge Request announcement was sent to slack channel');

        return self::SUCCESS;
    }
}
