<?php

namespace Turbine\Workflow\Deployment;

use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\Point;
use Turbine\Workflow\Configuration;

class DeploymentStatisticsUpdater
{
    private const ORG = 'Turbine Kreuzberg';
    private const BUCKET = 'devops-metrics';

    public function __construct(
        private Client $client,
        private Configuration $configuration
    ) {
    }

    public function update(string $deploymentType): void
    {
        $projectName = $this->configuration->get(Configuration::DEPLOYMENT_PROJECT_NAME);

        $writeApi = $this->client->createWriteApi();

        $point = Point::measurement('deployments')
            ->addTag('project', $projectName)
            ->addTag('type', $deploymentType)
            ->addField('deployment', 1);

        $writeApi->write($point, WritePrecision::S, self::BUCKET, self::ORG);
    }
}
