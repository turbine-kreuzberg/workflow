<?php

test('inspiring command', function () {
    namedMock()
    $this->artisan('inspiring')
         ->expectsOutput('Simplicity is the ultimate sophistication.')
         ->assertExitCode(0);
});
