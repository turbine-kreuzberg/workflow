<?php

use Turbine\Workflow\Configuration;
use Turbine\Workflow\Workflow\Provider\FavouriteTicketChoicesProvider;

test('Jira ', function () {
    $configurationMock = mock(Configuration::class);
    $configurationMock->shouldReceive('get')
        ->with('JIRA_FAVOURITE_TICKETS')
        ->once()
        ->andReturn('Test-123');
    $configurationMock->shouldReceive('get')
        ->with('JIRA_USERNAME')
        ->once()
        ->andReturn('Jira user');

    $FavouriteTicketChoicesProviderMock = mock(FavouriteTicketChoicesProvider::class);
    $FavouriteTicketChoicesProviderMock->shouldReceive('provide')
        ->once()
        ->andReturn(
            [
                0 => 'Test-123'
            ]
        );


    app()->instance(FavouriteTicketChoicesProvider::class, $FavouriteTicketChoicesProviderMock->makePartial());
    $this->artisan('jira:book:time')
        ->expectsChoice('What ticket do you want to book time on', 'Test-123', ['Test-123', 'Custom input'])
        ->expectsQuestion('How long you worked on the ticket', '30')
        ->expectsOutput('Booked 30 min on Test-123')
        ->assertExitCode(0);
});
