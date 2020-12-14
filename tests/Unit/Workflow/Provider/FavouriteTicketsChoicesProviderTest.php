<?php
namespace Unit\Workflow\Provider;

use PHPUnit\Framework\TestCase;
use Workflow\Configuration;
use Workflow\Transfers\JiraIssueTransfer;
use Workflow\Transfers\JiraIssueTransferCollection;
use Workflow\Workflow\Jira\IssueReader;
use Workflow\Workflow\Provider\FavouriteTicketChoicesProvider;

class FavouriteTicketsChoicesProviderTest extends TestCase
{
    public function testNoConfiguredFavouriteTicketsReturnsNoChoices(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('getConfiguration')
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
            ->method('getConfiguration')
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
            ->method('getConfiguration')
            ->with('JIRA_FAVOURITE_TICKETS')
            ->willReturn('test-123,ticket-123');

        $issueReaderMock = $this->createMock(IssueReader::class);
        $jiraIssueTransfer = (new JiraIssueTransfer());
        $jiraIssueTransfer->key = 'test-123';
        $jiraIssueTransfer->summary = 'test-123-summary';

        $issueReaderMock->expects(self::once())
            ->method('getIssues')
            ->with(['test-123', 'ticket-123'])
            ->willReturn(new JiraIssueTransferCollection([$jiraIssueTransfer]));

        $favouriteTicketProvider = new FavouriteTicketChoicesProvider($configurationMock, $issueReaderMock);

        self::assertEquals(['test-123' => 'test-123-summary'], $favouriteTicketProvider->provide());
    }
}
