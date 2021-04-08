<?php

namespace Turbine\Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Turbine\Workflow\Deployment\DeploymentStatisticsUpdater;

class DeploymentStatisticsCommand extends Command
{
    private const COMMAND_NAME = 'workflow:deployment:statistics:update';

    public function __construct(private DeploymentStatisticsUpdater $deploymentStatisticsUpdater)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Update deployment statistics.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->deploymentStatisticsUpdater->update('hotfix');

        return 0;
    }
}
