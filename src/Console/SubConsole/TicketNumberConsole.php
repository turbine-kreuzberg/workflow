<?php

namespace Turbine\Workflow\Console\SubConsole;

use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Workflow\Provider\FavouriteTicketChoicesProvider;
use Turbine\Workflow\Workflow\TicketIdProvider;

class TicketNumberConsole
{
    private const CUSTOM_INPUT_KEY = 'custom';
    private const CUSTOM_INPUT = 'Custom input';

    public function __construct(
        private TicketIdProvider $ticketIdProvider,
        private FavouriteTicketChoicesProvider $favouriteTicketChoicesProvider
    ) {
    }

    public function getIssueTicketNumber(SymfonyStyle $inputOutputStyle): string
    {
        $choices = $this->favouriteTicketChoicesProvider->provide();

        if (empty($choices)) {
            return $inputOutputStyle->ask('What ticket do you want to book time on? Ticket number');
        }

        $choices[self::CUSTOM_INPUT_KEY] = self::CUSTOM_INPUT;

        $choice = $inputOutputStyle->choice(
            'What ticket do you want to book time on',
            $choices,
            self::CUSTOM_INPUT_KEY
        );

        if ($choice !== self::CUSTOM_INPUT_KEY) {
            return $choice;
        }

        return $inputOutputStyle->ask('What ticket do you want to book time on? Ticket number');
    }
}
