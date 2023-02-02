<?php

declare(strict_types=1);

namespace Turbine\Workflow\Deployment;

use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\Point;
use Turbine\Workflow\Configuration;

class DeploymentStatisticsUpdater
{
    private const ORG = 'Turbine Kreuzberg';

    public function __construct(
        private Client $client,
        private Configuration $configuration
    ) {
    }

    public function update(string $deploymentType): void
    {
        $projectName = $this->configuration->get(Configuration::DEPLOYMENT_PROJECT_NAME);
        $deploymentBucket = $this->configuration->get(Configuration::DEPLOYMENT_BUCKET);

        $writeApi = $this->client->createWriteApi();

        $point = Point::measurement('deployments')
            ->addTag('project', $projectName)
            ->addTag('type', $deploymentType)
            ->addField('deployment', 1);

        $writeApi->write($point, WritePrecision::S, $deploymentBucket, self::ORG);
    }
}
