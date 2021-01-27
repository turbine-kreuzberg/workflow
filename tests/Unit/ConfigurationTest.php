<?php

namespace Unit\Workflow;

use PHPUnit\Framework\TestCase;
use Workflow\Configuration;

class ConfigurationTest extends TestCase
{
    public function testConfigurationCanNotBeFoundThrowsException(): void
    {
        $configuration = new Configuration();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Configuration with UNKNOWN_KEY was not found. Please check your ".env.dist" file how to create and use it'
        );

        $configuration->getConfiguration('UNKNOWN_KEY');
    }

    public function testKnownConfigurationReturned(): void
    {
        $configuration = new Configuration();
        $value = $configuration->getConfiguration('TEST_ENVIRONMENT_KEY');

        self::assertEquals('TEST_ENVIRONMENT_VALUE', $value);
    }

    public function testGetNonRemovableBranchesConfiguredByPhpUnitXml(): void
    {
        $configuration = new Configuration();
        $nonRemovableBranches = $configuration->getNonRemovableBranches();

        self::assertEquals(
            [
                'BRANCH_DEVELOPMENT',
                'BRANCH_DEPLOYMENT',
            ],
            $nonRemovableBranches
        );
    }
}