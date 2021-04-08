<?php

namespace Turbine\Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Turbine\Workflow\Deployment\DeploymentStatisticsUpdater;

class DeploymentStatisticsCommand extends Command
{
    private const COMMAND_NAME = 'workflow:deployment:statistics:update';
    private const HOTFIX = 'hotfix';
    private const REGULAR = 'regular';

    public function __construct(private DeploymentStatisticsUpdater $deploymentStatisticsUpdater)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Update deployment statistics.');

        $this->addOption(
            self::HOTFIX,
            null,
            InputOption::VALUE_NONE,
            'Use this option to enable fast worklog'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $deploymentType = self::REGULAR;
        if ((bool)$input->getOption(self::HOTFIX)) {
            $deploymentType = self::HOTFIX;
        }
        $this->deploymentStatisticsUpdater->update($deploymentType);

        return 0;
    }
}
