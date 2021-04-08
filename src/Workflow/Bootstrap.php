<?php

namespace Turbine\Workflow\Workflow;

use Symfony\Component\Console\Application;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Console\BookTimeCommand;
use Turbine\Workflow\Console\CreateJiraIssueCommand;
use Turbine\Workflow\Console\CreateMergeRequestCommand;
use Turbine\Workflow\Console\DeploymentStatisticsCommand;
use Turbine\Workflow\Console\GetJiraIssueDataCommand;
use Turbine\Workflow\Console\ListBookingsCommand;
use Turbine\Workflow\Console\MoveJiraIssueCommand;
use Turbine\Workflow\Console\WorkOnTicketCommand;
use Turbine\Workflow\Deployment\DeploymentFactory;

class Bootstrap
{
    public function run(): void
    {
        $application = new Application();
        $workflowFactory = new WorkflowFactory();
        $deploymentFactory = new DeploymentFactory();
        $application->add(
            new BookTimeCommand(
                configuration: new Configuration(),
                workflowFactory: $workflowFactory,
                issueUpdater: $workflowFactory->createJiraIssueUpdater(),
                issueReader: $workflowFactory->createJiraIssueReader(),
                fastBookTimeConsole: $workflowFactory->createFastBookTimeConsole(),
                ticketIdProvider: $workflowFactory->getTicketIdProvider(),
                ticketNumberConsole: $workflowFactory->createTicketNumberConsole(),
                worklogCommentConsole: $workflowFactory->createWorklogCommentConsole()
            )
        );
        $application->add(
            new ListBookingsCommand(
                jiraIssueReader: $workflowFactory->createJiraIssueReader(),
                workflowFactory: $workflowFactory
            )
        );
        $application->add(new CreateJiraIssueCommand(name: null, workflowFactory: $workflowFactory));
        $application->add(
            new WorkOnTicketCommand(
                workflowFactory: $workflowFactory,
                branchNameValidator: $workflowFactory->createBranchNameValidator(),
                workOnTicket: $workflowFactory->createWorkOnTicket(),
                branchNameProvider: $workflowFactory->createBranchNameProvider()
            )
        );
        $application->add(
            new CreateMergeRequestCommand(
                mergeRequestCreator: $workflowFactory->createMergeRequestCreator(),
                workflowFactory: $workflowFactory
            )
        );
        $application->add(
            new GetJiraIssueDataCommand(
                workflowFactory: $workflowFactory,
                issueReader: $workflowFactory->createJiraIssueReader(),
            )
        );

        $application->add(
            new MoveJiraIssueCommand(
                workflowFactory: $workflowFactory,
                issueUpdater: $workflowFactory->createJiraIssueUpdater(),
            )
        );
        $application->add(new DeploymentStatisticsCommand($deploymentFactory->createDeploymentStatisticsUpdater()));

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
