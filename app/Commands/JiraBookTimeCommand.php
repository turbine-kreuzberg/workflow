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
use Turbine\Workflow\Workflow\TicketIdProvider;
use Turbine\Workflow\Workflow\WorkflowFactory;

class JiraBookTimeCommand extends Command
{
    private const FAST_WORKLOG = 'fast-worklog';

    public function __construct(
        private WorkflowFactory $workflowFactory,
        private IssueUpdater $issueUpdater,
        private IssueReader $issueReader,
        private FastBookTimeConsole $fastBookTimeConsole,
        private TicketIdProvider $ticketIdProvider,
        private TicketNumberConsole $ticketNumberConsole,
        private WorklogCommentConsole $worklogCommentConsole
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
    public function handle()
    {
        new BuildStatusTransfer(...$this->options());
        $today = date('Y-m-d') . 'T12:00:00.000+0000';

        $fastWorkflow = $this->option(self::FAST_WORKLOG);
        if ($fastWorkflow && $this->fastBookTimeConsole->execFastBooking($inputOutputStyle, $today))
        {
            return 0;
        }

        $issueNumber = $this->getIssueTicketNumber($input, $inputOutputStyle);

        $issue = $this->issueReader->getIssue($issueNumber);
        $inputOutputStyle->title(\sprintf('Book time on ticket: %s - %s', $issue->key, $issue->summary));

        $worklogComment = $this->worklogCommentConsole->createWorklogComment($issueNumber, $inputOutputStyle);

        try {
            $lastTicketWorklog = $this->issueReader->getLastTicketWorklog($issueNumber);
            $duration = $this->createWorklogDuration($lastTicketWorklog, $inputOutputStyle);
        } catch (JiraNoWorklogException $jiraNoWorklogException) {
            $duration = $inputOutputStyle->ask('For how long did you do it');
        }

        $bookedTimeInMinutes = $this->issueUpdater->bookTime(
            $issueNumber,
            $worklogComment,
            $duration,
            $today
        );

        $inputOutputStyle->success(
            'Booked '
            . $bookedTimeInMinutes
            . ' minutes for "'
            . $worklogComment
            . '" on '
            . $issueNumber
            . "\nTotal booked time today: "
            . $this->issueReader->getTimeSpentToday()
            . 'h'
        );
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
