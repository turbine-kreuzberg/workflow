<?php

namespace Turbine\Workflow\Console;

use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\Point;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeploymentStatisticsCommand extends Command
{

    protected static $defaultName = 'workflow:deployment:statistics:update';


    protected function configure(): void
    {
        $this->setDescription('Update deployment statistics.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        // You can generate a Token from the "Tokens Tab" in the UI
        // environment variable
        $token = 'XXge6_po0WlpG0kRJzhLhTdELM40EuhPqrZN1ZZuoDgmEzKOo97m-nj74lXP355UV3cRmUQf_tN5rsG1MGSDZQ==';

        // constants
        $org = 'Turbine Kreuzberg';
        $bucket = 'devops-metrics';
        $url = "https://influxdb-devops-metrics.akropolis.turbinekreuzberg.io";

        $client = new Client(
            [
            "url" => $url,
            "token" => $token,
            ]
        );

        $projectName = "Simplicity";
        $deploymentType = "hotfix";

        $data = "deployments,project=" . $projectName . ",type=" . $deploymentType . " deployment=1";
        $writeApi = $client->createWriteApi();

        $point = Point::measurement('deployments')
            ->addTag('project', $projectName)
            ->addTag('type', $deploymentType)
            ->addField('deployment', 1)
            ->time(microtime(true));

        $writeApi->write($data, WritePrecision::S, $bucket, $org);

        return 0;
    }
}
