<?php

namespace Unit\Workflow;

use PHPUnit\Framework\TestCase;
use Workflow\Client\GitClient;
use Workflow\Workflow\BookTime;
use Workflow\Workflow\TicketIdentifier;

class BookTimeTest extends TestCase
{
    public function testExtractTicketIdFromCurrentBranch(): void
    {
        $gitClientMock = $this->createMock(GitClient::class);
        $gitClientMock->expects(self::once())
            ->method('getCurrentBranchName')
            ->willReturn('testBranch');
        
        $ticketIdentifierMock = $this->createMock(TicketIdentifier::class);
        $ticketIdentifierMock->expects(self::once())
            ->method('extractFromBranchName')
            ->with('testBranch')
            ->willReturn('test');

        $bookTime = new BookTime($gitClientMock, $ticketIdentifierMock);

        self::assertEquals('test', $bookTime->extractTicketIdFromCurrentBranch());
    }
}
