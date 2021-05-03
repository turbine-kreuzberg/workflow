<?php

namespace Unit\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Turbine\Workflow\Console\DeploymentStatisticsCommand;
use Turbine\Workflow\Deployment\DeploymentStatisticsUpdater;
use Turbine\Workflow\Workflow\Provider\CommitMessageProvider;

class DeploymentStatisticsCommandTest extends TestCase
{
    public function testUpdateDeploymentWithRegularDeploy(): void
    {
        $deploymentStatisticsUpdaterMock = $this->createMock(DeploymentStatisticsUpdater::class);
        $deploymentStatisticsUpdaterMock->expects(self::once())
            ->method('update')
            ->with('regular');

        $commitMessageProviderMock = $this->createMock(CommitMessageProvider::class);

        $deploymentStatisticsCommand = new DeploymentStatisticsCommand(
            $deploymentStatisticsUpdaterMock,
            $commitMessageProviderMock
        );

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

        $commitMessageProviderMock = $this->createMock(CommitMessageProvider::class);

        $deploymentStatisticsCommand = new DeploymentStatisticsCommand(
            $deploymentStatisticsUpdaterMock,
            $commitMessageProviderMock
        );

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->expects(self::once())
            ->method('getOption')
            ->with()
            ->willReturn(true);

        $outputMock = $this->createMock(OutputInterface::class);

        self::assertSame(0, $deploymentStatisticsCommand->run($inputMock, $outputMock));
    }

    public function testUpdateDeploymentWithHotfixCommitMessage(): void
    {
        $deploymentStatisticsUpdaterMock = $this->createMock(DeploymentStatisticsUpdater::class);
        $deploymentStatisticsUpdaterMock->expects(self::once())
            ->method('update')
            ->with('hotfix');

        $commitMessageProviderMock = $this->createMock(CommitMessageProvider::class);
        $commitMessageProviderMock->expects(self::once())
            ->method('isHotfixCommitMessage')
            ->willReturn(true);

        $deploymentStatisticsCommand = new DeploymentStatisticsCommand(
            $deploymentStatisticsUpdaterMock,
            $commitMessageProviderMock
        );

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->expects(self::once())
            ->method('getOption')
            ->with()
            ->willReturn(false);

        $outputMock = $this->createMock(OutputInterface::class);

        self::assertSame(0, $deploymentStatisticsCommand->run($inputMock, $outputMock));
    }
}
