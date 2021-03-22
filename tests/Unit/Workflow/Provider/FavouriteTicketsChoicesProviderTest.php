<?php

namespace Unit\Workflow\Provider;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Transfers\JiraIssueTransfer;
use Turbine\Workflow\Transfers\JiraIssueTransferCollection;
use Turbine\Workflow\Workflow\Jira\IssueReader;
use Turbine\Workflow\Workflow\Provider\FavouriteTicketChoicesProvider;

class FavouriteTicketsChoicesProviderTest extends TestCase
{
    public function testNoConfiguredFavouriteTicketsReturnsNoChoices(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('get')
            ->with('JIRA_FAVOURITE_TICKETS')
            ->willReturn('');

        $issueReaderMock = $this->createMock(IssueReader::class);
        $issueReaderMock->expects(self::never())
            ->method('getIssues');

        $favouriteTicketProvider = new FavouriteTicketChoicesProvider($configurationMock, $issueReaderMock);

        self::assertEquals([], $favouriteTicketProvider->provide());
    }

    public function testIssueReaderFindsNoJiraIssuesReturnsNoChoices(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('get')
            ->with('JIRA_FAVOURITE_TICKETS')
            ->willReturn('test-123,ticket-123');
        $issueReaderMock = $this->createMock(IssueReader::class);

        $issueReaderMock->expects(self::once())
            ->method('getIssues')
            ->willReturn(new JiraIssueTransferCollection([]));

        $favouriteTicketProvider = new FavouriteTicketChoicesProvider($configurationMock, $issueReaderMock);

        self::assertEquals([], $favouriteTicketProvider->provide());
    }

    public function testProvideFavouriteTickets(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('get')
            ->with('JIRA_FAVOURITE_TICKETS')
            ->willReturn('ticket-1,ticket-2');

        $issueReaderMock = $this->createMock(IssueReader::class);
        $jiraIssueTransfer = (new JiraIssueTransfer());
        $jiraIssueTransfer->key = 'ticket-1';
        $jiraIssueTransfer->summary = 'ticket-1-summary';

        $jiraIssueTransfer2 = (new JiraIssueTransfer());
        $jiraIssueTransfer2->key = 'ticket-2';
        $jiraIssueTransfer2->summary = 'ticket-2-summary';

        $issueReaderMock->expects(self::once())
            ->method('getIssues')
            ->with(['ticket-1', 'ticket-2'])
            ->willReturn(
                new JiraIssueTransferCollection(
                    [
                        $jiraIssueTransfer,
                        $jiraIssueTransfer2,
                    ]
                )
            );

        $favouriteTicketProvider = new FavouriteTicketChoicesProvider($configurationMock, $issueReaderMock);

        self::assertEquals(
            [
                'ticket-1' => 'ticket-1-summary',
                'ticket-2' => 'ticket-2-summary',
            ],
            $favouriteTicketProvider->provide()
        );
    }
}
