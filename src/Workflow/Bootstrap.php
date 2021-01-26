<?php

namespace Workflow\Workflow;

use Symfony\Component\Console\Application;
use Workflow\Client\ClientFactory;
use Workflow\Configuration;
use Workflow\Console\BookTimeCommand;
use Workflow\Console\CreateJiraIssueCommand;
use Workflow\Console\CreateMergeRequestCommand;
use Workflow\Console\WorkOnTicketCommand;

class Bootstrap
{
    public function run(): void
    {
        $application = new Application();
        $application->add(new BookTimeCommand(configuration: new Configuration(), name: null));
        $workflowFactory = new WorkflowFactory();
        $application->add(new CreateJiraIssueCommand(workflowFactory: $workflowFactory, name: null));
        $application->add(new WorkOnTicketCommand(workflowFactory: $workflowFactory, name: null));
        $application->add(
            new CreateMergeRequestCommand(
                mergeRequestCreator: $workflowFactory->createMergeRequestCreator()
            )
        );

        /**
         * $application->add(new CheckBranchStatusCommand());
         * $application->add(new SetBranchAccessLevelCommand());
         * $application->add(new AcceptMergeRequestCommand());
         * $application->add(new DeploymentCreateMergeRequestCommand());
         * $application->add(new StageDeployCommand());
         * $application->add(new LiveDeployCommand());
         * $application->add(new DeployCommand());
         * $application->add(new TicketStatusCommand());
         * $application->add(new WorkflowCreateMergeRequestCommand());
         **/
        $application->run();
    }
}
