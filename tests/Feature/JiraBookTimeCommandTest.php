<?php

use Turbine\Workflow\Configuration;

test('Jira ', function () {
    $configurationMock = mock(Configuration::class)
        ->shouldReceive('get')
        ->with('JIRA_FAVOURITE_TICKETS')
        ->once()
        ->andReturn('Test-123');
//    app()->instance(Configuration::class, $configurationMock);
$this->instance(Configuration::class, $configurationMock);
    $this->artisan('jira:book:time')
        ->expectsChoice('What ticket do you want to book time on', 'Test-123', ['Test-123', 'Custom input'])
        ->expectsQuestion('How long you worked on the ticket', '30')
        ->expectsOutput('Booked 30 min on Test-123')
        ->assertExitCode(0);
});
