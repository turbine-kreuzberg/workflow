<?php
namespace Unit\Workflow\Provider;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Transfers\JiraIssueTransfer;
use Turbine\Workflow\Transfers\JiraIssueTransferCollection;
use Turbine\Workflow\Workflow\Jira\IssueReader;
use Turbine\Workflow\Workflow\Provider\FavouriteTicketChoicesProvider;
use Turbine\Workflow\Workflow\Provider\FavouriteWorklogCommentChoicesProvider;

class FavouriteWorklogCommentChoicesProviderTest extends TestCase
{
    public function testNoConfiguredFavouriteWorklogCommentsReturnsNoChoices(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('get')
            ->with('FAVOURITE_WORKLOG_COMMENTS')
            ->willReturn('');

        $favouriteWorklogCommentsProvider = new FavouriteWorklogCommentChoicesProvider($configurationMock);

        self::assertEquals([], $favouriteWorklogCommentsProvider->provide());
    }

    public function testProvideFavouriteWorklogComments(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('get')
            ->with('FAVOURITE_WORKLOG_COMMENTS')
            ->willReturn('test-123,comment-123');

        $favouriteWorklogCommentsProvider = new FavouriteWorklogCommentChoicesProvider($configurationMock);

        self::assertEquals(['test-123', 'comment-123'], $favouriteWorklogCommentsProvider->provide());
    }
}
