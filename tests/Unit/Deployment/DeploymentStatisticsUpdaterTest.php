<?php

namespace Unit\Deployment;

use InfluxDB2\Client;
use InfluxDB2\WriteApi;
use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Deployment\DeploymentStatisticsUpdater;

class DeploymentStatisticsUpdaterTest extends TestCase
{
    public function testUpdateDeploymentStatistics(): void
    {
        $writeApiMock = $this->createMock(WriteApi::class);
        $writeApiMock->expects(self::once())
            ->method('write')
            ->with(
                'deployments,project=projectName,type=deploymentType deployment=1',
                's',
                'devops-metrics',
                'Turbine Kreuzberg'
            );

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects(self::once())
            ->method('createWriteApi')
            ->willReturn($writeApiMock);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('get')
            ->with('PROJECT_NAME')
            ->willReturn('projectName');
        $deploymentStatisticsUpdater = new DeploymentStatisticsUpdater(
            $clientMock,
            $configurationMock
        );

        $deploymentStatisticsUpdater->update('deploymentType');
    }
}
