<?php

namespace Unit\Deployment;

use InfluxDB2\Client;
use InfluxDB2\Point;
use InfluxDB2\WriteApi;
use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Deployment\DeploymentStatisticsUpdater;

class DeploymentStatisticsUpdaterTest extends TestCase
{
    public function testUpdateDeploymentStatistics(): void
    {
        $point = Point::measurement('deployments')
            ->addTag('project', 'projectName')
            ->addTag('type', 'deploymentType')
            ->addField('deployment', 1);

        $writeApiMock = $this->createMock(WriteApi::class);
        $writeApiMock->expects(self::once())
            ->method('write')
            ->with(
                $point,
                's',
                'deployment_bucket',
                'Turbine Kreuzberg'
            );

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects(self::once())
            ->method('createWriteApi')
            ->willReturn($writeApiMock);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['DEPLOYMENT_PROJECT_NAME'], ['DEPLOYMENT_BUCKET'])
            ->willReturnOnConsecutiveCalls('projectName', 'deployment_bucket');
        $deploymentStatisticsUpdater = new DeploymentStatisticsUpdater(
            $clientMock,
            $configurationMock
        );

        $deploymentStatisticsUpdater->update('deploymentType');
    }
}
