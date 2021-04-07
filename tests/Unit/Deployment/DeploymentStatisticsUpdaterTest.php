<?php

namespace Unit\Deployment;

use InfluxDB2\Client;
use InfluxDB2\WriteApi;
use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Deployment\DeploymentStatisticsUpdater;

class DeploymentStatisticsUpdaterTest extends TestCase
{
    public function testUpdateDeploymentStatistics(): void
    {
        $writeApiMock = $this->createMock(WriteApi::class);
        $writeApiMock->expects(self::once())
            ->method('write')
            ->with('deployments,project=Simplicity,type=hotfix deployment=1');
        
        $clientMock = $this->createMock(Client::class);
        $clientMock->expects(self::once())
            ->method('createWriteApi')
            ->willReturn($writeApiMock);
        $deploymentStatisticsUpdater = new DeploymentStatisticsUpdater(
            $clientMock
        );

        $deploymentStatisticsUpdater->update();
    }
}
