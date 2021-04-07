<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Turbine\Workflow\Console\DeploymentStatisticsCommand;
use Turbine\Workflow\Deployment\DeploymentStatisticsUpdater;

class DeploymentStatisticsCommandTest extends TestCase
{
    public function testUpdateDeploymentWithRegularDeploy(): void
    {
        $deploymentStatisticsUpdaterMock = $this->createMock(DeploymentStatisticsUpdater::class);
        $deploymentStatisticsUpdaterMock->expects(self::once())
            ->method('update')
            ->with('regular');
        $deploymentStatisticsCommand = new DeploymentStatisticsCommand($deploymentStatisticsUpdaterMock);

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->expects(self::once())
            ->method('getOption')
            ->with()
            ->willReturn(false);

        $outputMock = $this->createMock(OutputInterface::class);

        self::assertSame(0, $deploymentStatisticsCommand->run($inputMock, $outputMock));
    }

    public function testUpdateDeploymentWithHotfixDeploy(): void
    {
        $deploymentStatisticsUpdaterMock = $this->createMock(DeploymentStatisticsUpdater::class);
        $deploymentStatisticsUpdaterMock->expects(self::once())
            ->method('update')
            ->with('hotfix');
        $deploymentStatisticsCommand = new DeploymentStatisticsCommand($deploymentStatisticsUpdaterMock);

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->expects(self::once())
            ->method('getOption')
            ->with()
            ->willReturn(true);

        $outputMock = $this->createMock(OutputInterface::class);

        self::assertSame(0, $deploymentStatisticsCommand->run($inputMock, $outputMock));
    }
}
