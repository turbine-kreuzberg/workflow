<?php

use PHPUnit\Framework\TestCase;
use Workflow\Configuration;
use Workflow\Transfers\JiraIssueTransferCollection;
use Workflow\Workflow\Jira\IssueReader;

class FavouriteTicketsProviderTest extends TestCase
{
    /**
     * @skip
     *
     * @return void
     */
    public function testProvideFavouriteTickets(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('getFavouriteTicketsFromEnvironment')
            ->willReturn('test-123,ticket-123');

        $issueReaderMock = $this->createMock(IssueReader::class);
        $issueReaderMock->expects(self::once())
            ->method('getIssues')
            ->with([])
            ->willReturn(new JiraIssueTransferCollection([]));

        $favouriteTicketProvider = new FavouriteTicketProvider($configurationMock, $issueReaderMock);

        self::assertEquals([], $favouriteTicketProvider->provide());
    }
}
