<?php

namespace Unit\Console\SubConsole;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Console\SubConsole\TicketNumberConsole;
use Turbine\Workflow\Workflow\Provider\FavouriteTicketChoicesProvider;
use Turbine\Workflow\Workflow\TicketIdProvider;

class TicketNumberConsoleTest extends TestCase
{
    public function testGetIssueTicketNumberWithoutAnyChoicesWillAskForTicketNumber(): void
    {
        $ticketIdProviderMock = $this->createMock(TicketIdProvider::class);

        $favouriteTicketChoicesProviderMock = $this->createMock(FavouriteTicketChoicesProvider::class);
        $favouriteTicketChoicesProviderMock->expects(self::once())
            ->method('provide')
            ->willReturn([]);

        $ticketNumberConsole = new TicketNumberConsole(
            $ticketIdProviderMock,
            $favouriteTicketChoicesProviderMock
        );

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('ask')
            ->with('What ticket do you want to book time on? Ticket number')
            ->willReturn('Ticket number');

        self::assertEquals('Ticket number', $ticketNumberConsole->getIssueTicketNumber($symfonyStyleMock));
    }

    public function testGetIssueTicketNumberFromChoices(): void
    {
        $ticketIdProviderMock = $this->createMock(TicketIdProvider::class);

        $favouriteTicketChoicesProviderMock = $this->createMock(FavouriteTicketChoicesProvider::class);
        $favouriteTicketChoicesProviderMock->expects(self::once())
            ->method('provide')
            ->willReturn(
                [
                    'ticket-134',
                    'ticket-999',
                ]
            );

        $ticketNumberConsole = new TicketNumberConsole(
            $ticketIdProviderMock,
            $favouriteTicketChoicesProviderMock
        );

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('choice')
            ->with(
                'What ticket do you want to book time on',
                [
                    'ticket-134',
                    'ticket-999',
                    'custom' => 'Custom input',
                ],
                'custom'
            )
            ->willReturn('ticket-134');

        self::assertEquals('ticket-134', $ticketNumberConsole->getIssueTicketNumber($symfonyStyleMock));
    }

    public function testGetIssueTicketNumberWithChoicesButSelectCustomInput(): void
    {
        $ticketIdProviderMock = $this->createMock(TicketIdProvider::class);

        $favouriteTicketChoicesProviderMock = $this->createMock(FavouriteTicketChoicesProvider::class);
        $favouriteTicketChoicesProviderMock->expects(self::once())
            ->method('provide')
            ->willReturn(
                [
                    'ticket-134',
                    'ticket-999',
                ]
            );

        $ticketNumberConsole = new TicketNumberConsole(
            $ticketIdProviderMock,
            $favouriteTicketChoicesProviderMock
        );

        $symfonyStyleMock = $this->createMock(SymfonyStyle::class);
        $symfonyStyleMock->expects(self::once())
            ->method('choice')
            ->with(
                'What ticket do you want to book time on',
                [
                    'ticket-134',
                    'ticket-999',
                    'custom' => 'Custom input',
                ],
                'custom'
            )
            ->willReturn('custom');

        $symfonyStyleMock->expects(self::once())
            ->method('ask')
            ->with('What ticket do you want to book time on? Ticket number')
            ->willReturn('Ticket number');

        self::assertEquals('Ticket number', $ticketNumberConsole->getIssueTicketNumber($symfonyStyleMock));
    }
}
