<?php

namespace Workflow\Workflow;

use Symfony\Component\Console\Application;
use Workflow\Console\BookTimeCommand;

class Bootstrap
{
    public function run(): void
    {
        $application = new Application();
        $application->add(new BookTimeCommand());
        /**
* $application->add(new CheckBranchStatusCommand());
        $application->add(new SetBranchAccessLevelCommand());
        $application->add(new AcceptMergeRequestCommand());
        $application->add(new DeploymentCreateMergeRequestCommand());
        $application->add(new StageDeployCommand());
        $application->add(new LiveDeployCommand());
        $application->add(new DeployCommand());
        $application->add(new TicketStatusCommand());
        $application->add(new CreateJiraIssueCommand());
        $application->add(new WorkflowCreateMergeRequestCommand());
        $application->add(new WorkOnTicketCommand());
         **/
        $application->run();
    }
}
