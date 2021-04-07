<?php

namespace Deployment;

use InfluxDB2\Client;
use Turbine\Workflow\Deployment\DeploymentStatisticsUpdater;

class DeploymentFactory
{
    private const TOKEN = 'XXge6_po0WlpG0kRJzhLhTdELM40EuhPqrZN1ZZuoDgmEzKOo97m-nj74lXP355UV3cRmUQf_tN5rsG1MGSDZQ==';

    public function createDeploymentStatisticsUpdater(): DeploymentStatisticsUpdater
    {
        return new DeploymentStatisticsUpdater(
            new Client(
                [
                    "url" => 'https://influxdb-devops-metrics.akropolis.turbinekreuzberg.io',
                    "token" => self::TOKEN,
                ]
            )
        );
    }
}
