<?php

namespace Turbine\Workflow\Deployment;

use InfluxDB2\Client;
use Turbine\Workflow\Configuration;

class DeploymentFactory
{
    private ?Configuration $configuration = null;

    public function createDeploymentStatisticsUpdater(): DeploymentStatisticsUpdater
    {
        return new DeploymentStatisticsUpdater(
            new Client(
                [
                    "url" => 'https://influxdb-devops-metrics.akropolis.turbinekreuzberg.io',
                    "token" => $this->createConfiguration()->get('INFLUX_DB_TOKEN'),
                ]
            ),
            $this->createConfiguration()
        );
    }

    private function createConfiguration(): Configuration
    {
        if ($this->configuration === null) {
            $this->configuration = new Configuration();
        }

        return $this->configuration;
    }
}
