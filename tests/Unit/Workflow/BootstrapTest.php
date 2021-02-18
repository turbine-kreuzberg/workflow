<?php

namespace Unit\Workflow;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Workflow\Bootstrap;

class BootstrapTest extends TestCase
{
    public function testWorkflowCommandsExist(): void
    {
        $output = (\shell_exec('/var/www/bin/workflow'));

        self::assertStringEqualsFile('tests/Unit/fixtures/workflow_commands.txt', (string)$output);
    }
}