<?php

namespace Turbine\Workflow\Workflow;

use Symfony\Component\Console\Application;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Console\BookTimeCommand;
use Turbine\Workflow\Console\CreateJiraIssueCommand;
use Turbine\Workflow\Console\CreateMergeRequestCommand;
use Turbine\Workflow\Console\GetJiraIssueDataCommand;
use Turbine\Workflow\Console\ListBookingsCommand;
use Turbine\Workflow\Console\WorkOnTicketCommand;
use Turbine\Workflow\Workflow\Jira\IssueReader;

class Bootstrap
{
    public function run(): void
    {
        $application = new Application();
        $application->add(new BookTimeCommand(configuration: new Configuration(), name: null));
        $workflowFactory = new WorkflowFactory();
        $application->add(
            new ListBookingsCommand(
                configuration: new Configuration(),
                jiraIssueReader: $workflowFactory->createJiraIssueReader(),
                name: null
            )
        );
        $application->add(new CreateJiraIssueCommand(workflowFactory: $workflowFactory, name: null));
        $application->add(new WorkOnTicketCommand(workflowFactory: $workflowFactory, name: null));
        $application->add(
            new CreateMergeRequestCommand(
                mergeRequestCreator: $workflowFactory->createMergeRequestCreator()
            )
        );
        $application->add(
            new GetJiraIssueDataCommand(
                workflowFactory: $workflowFactory,
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
