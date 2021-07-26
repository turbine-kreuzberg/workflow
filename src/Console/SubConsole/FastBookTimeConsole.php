<?php

declare(strict_types=1);

namespace Turbine\Workflow\Console\SubConsole;

use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Workflow\Jira\IssueReader;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\Provider\FastWorklogProvider;

class FastBookTimeConsole
{
    public function __construct(
        private FastWorklogProvider $fastWorklogProvider,
        private IssueUpdater $issueUpdater,
        private IssueReader $issueReader,
    ) {
    }

    public function execFastBooking(
        SymfonyStyle $inputOutputStyle,
        string $today
    ): bool {
        [$issue, $worklogComment] = $this->fastWorklogProvider->provide();

        if (! isset($issue, $worklogComment)) {
            return false;
        }
        $questionFastWorklog = sprintf(
            'How much time do you want to book on <fg=yellow>[%s]</> with message <fg=yellow>"%s"</>',
            $issue,
            $worklogComment
        );
        $duration = $inputOutputStyle->ask($questionFastWorklog);
        $bookedTimeInMinutes = $this
            ->issueUpdater
            ->bookTime($issue, $worklogComment, $duration, $today);
        $inputOutputStyle->success(
            'Booked '
            . $bookedTimeInMinutes
            . ' minutes for "'
            . $worklogComment
            . '" on '
            . $issue
            . "\nTotal booked time today: "
            . $this->issueReader->getTimeSpentToday()
            . 'h'
        );

        return true;
    }
}
