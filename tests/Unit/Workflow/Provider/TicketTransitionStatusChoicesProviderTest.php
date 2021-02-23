<?php
namespace Unit\Workflow\Provider;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Workflow\Jira\IssueReader;
use Turbine\Workflow\Workflow\Provider\TicketTransitionStatusChoicesProvider;

class TicketTransitionStatusChoicesProviderTest extends TestCase
{
    public function testProvideTicketTransitionStatus(): void
    {
        $testTicketNumber = '1234';

        $issueReaderMock = $this->createMock(IssueReader::class);
        $issueReaderMock->expects(self::once())
            ->method('getIssueTransitions')
            ->with($testTicketNumber)
            ->willReturn(
                [
                    'transitions' => [
                        ['to' => ['name' => 'status1']],
                        ['to' => ['name' => 'status2']],
                    ]
                ]
            );

        $ticketTransitionsStatusProvider = new TicketTransitionStatusChoicesProvider($issueReaderMock);

        self::assertEqualsCanonicalizing(
            ['status1', 'status2'],
            $ticketTransitionsStatusProvider->provide($testTicketNumber)
        );
    }

    public function testProvideTicketTransitionStatusWillReturnNoChoicesIfJiraIssueNotFound(): void
    {
        $testTicketNumber = '1234';

        $issueReaderMock = $this->createMock(IssueReader::class);

        $ticketTransitionsStatusProvider = new TicketTransitionStatusChoicesProvider($issueReaderMock);

        self::assertEquals([], $ticketTransitionsStatusProvider->provide($testTicketNumber));
    }
}
