<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Turbine\Workflow\Console\DeploymentStatisticsCommand;
use Turbine\Workflow\Deployment\DeploymentStatisticsUpdater;

class DeploymentStatisticsCommandTest extends TestCase
{
    public function testUpdateDeployment(): void
    {
        $deploymentStatisticsUpdaterMock = $this->createMock(DeploymentStatisticsUpdater::class);
        $deploymentStatisticsUpdaterMock->expects(self::once())
            ->method('update')
            ->with('hotfix');
        $deploymentStatisticsCommand = new DeploymentStatisticsCommand($deploymentStatisticsUpdaterMock);

        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        self::assertSame(0, $deploymentStatisticsCommand->run($inputMock, $outputMock));
    }
}
