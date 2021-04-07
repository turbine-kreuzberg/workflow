<?php

namespace Turbine\Workflow\Deployment;

use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\Point;

class DeploymentStatisticsUpdater
{
    public function __construct(private Client $client)
    {
    }


    public function update(): void
    {
        // constants
        $org = 'Turbine Kreuzberg';
        $bucket = 'devops-metrics';


        $projectName = "Simplicity";
        $deploymentType = "hotfix";

        $data = "deployments,project=" . $projectName . ",type=" . $deploymentType . " deployment=1";
        $writeApi = $this->client->createWriteApi();

        $point = Point::measurement('deployments')
            ->addTag('project', $projectName)
            ->addTag('type', $deploymentType)
            ->addField('deployment', 1)
            ->time(microtime(true));

        $writeApi->write($data, WritePrecision::S, $bucket, $org);
    }
}
