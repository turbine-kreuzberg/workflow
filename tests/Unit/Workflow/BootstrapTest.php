<?php

namespace Unit\Workflow;

use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testWorkflowCommandsExist(): void
    {
        $output = (\shell_exec('bin/workflow'));

        self::assertStringEqualsFile('tests/Unit/fixtures/workflow_commands.txt', (string)$output);
    }
}
