<?php

declare(strict_types=1);

namespace Turbine\Workflow\Workflow\Provider;

use Exception;
use Throwable;
use Turbine\Workflow\Workflow\Jira\IssueReader;

class TicketTransitionStatusChoicesProvider
{
    public function __construct(private IssueReader $issueReader)
    {
    }

    public function provide(string $issue): array
    {
        $transitionChoices = [];

        try {
            $transitions = $this->issueReader->getIssueTransitions($issue);
            foreach ($transitions['transitions'] as $transition) {
                $transitionChoices[] = $transition['to']['name'];
            }
        } catch (Throwable | Exception $exception) {
            return [];
        }

        return $transitionChoices;
    }
}
