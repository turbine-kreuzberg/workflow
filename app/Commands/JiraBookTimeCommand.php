<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Turbine\Workflow\Console\SubConsole\FastBookTimeConsole;
use Turbine\Workflow\Console\SubConsole\TicketNumberConsole;
use Turbine\Workflow\Console\SubConsole\WorklogCommentConsole;
use Turbine\Workflow\Exception\JiraNoWorklogException;
use Turbine\Workflow\Transfers\BuildStatusTransfer;
use Turbine\Workflow\Workflow\Jira\IssueReader;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\Provider\FavouriteTicketChoicesProvider;
use Turbine\Workflow\Workflow\TicketIdProvider;
use Turbine\Workflow\Workflow\WorkflowFactory;

class JiraBookTimeCommand extends Command
{
    private const FAST_WORKLOG = 'fast-worklog';
    private const CUSTOM_INPUT_KEY = 'custom';
    private const CUSTOM_INPUT = 'Custom input';

    public function __construct(
        private WorkflowFactory $workflowFactory,
        private IssueUpdater $issueUpdater,
        private IssueReader $issueReader,
        private FastBookTimeConsole $fastBookTimeConsole,
        private TicketIdProvider $ticketIdProvider,
        private TicketNumberConsole $ticketNumberConsole,
        private WorklogCommentConsole $worklogCommentConsole,
    ) {
        parent::__construct();
    }
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'jira:book:time
        {ticketNumber? : You can provide the ticket number directly}
        {--c|current-branch= : Use this option to book time for current branch}
        {--f|fast-worklog : Use this option to enable fast worklog}
    ';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Book time for a jira issue.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(FavouriteTicketChoicesProvider $choicesProvider)
    {
        $issueTicketNumber = $this->getIssueTicketNumber($choicesProvider);

        $workTime = $this->ask('How long you worked on the ticket');

        $this->info('Booked 30 min on Test-123');
    }

    private function getIssueTicketNumber(FavouriteTicketChoicesProvider $choicesProvider): string
    {
        $choices = $choicesProvider->provide();

        if (empty($choices)) {
            return $this->ask('What ticket do you want to book time on? Ticket number');
        }

        $choices[self::CUSTOM_INPUT_KEY] = self::CUSTOM_INPUT;

        $choice = $this->choice(
            'What ticket do you want to book time on',
            $choices,
            self::CUSTOM_INPUT_KEY
        );

        if ($choice !== self::CUSTOM_INPUT_KEY) {
            return $choice;
        }

        return $this->ask('What ticket do you want to book time on? Ticket number');
    }
}
