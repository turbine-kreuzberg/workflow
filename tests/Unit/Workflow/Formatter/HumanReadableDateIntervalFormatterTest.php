<?php

namespace Unit\Workflow\Formatter;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Workflow\Formatter\HumanReadableDateIntervalFormatter;

class HumanReadableDateIntervalFormatterTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFormatTimeInSecondsToHumanReadableTime(string $timeInSeconds, string $expectedTimeFormat): void
    {
        $humanReadableDateIntervalFormatter = new HumanReadableDateIntervalFormatter();
        self::assertSame($expectedTimeFormat, $humanReadableDateIntervalFormatter->format($timeInSeconds));
    }

    public function provideData(): array
    {
        return [
            'empty time' => [
                'timeInSeconds' => '',
                'expectedTimeFormat' => '',
            ],
            '1 second' => [
                'timeInSeconds' => '1',
                'expectedTimeFormat' => '1s',
            ],
            '1 minute' => [
                'timeInSeconds' => '60',
                'expectedTimeFormat' => '1m',
            ],
            '1 minute and 1 second' => [
                'timeInSeconds' => '61',
                'expectedTimeFormat' => '1m 1s',
            ],
            '1 hour 1 minute and 1 second' => [
                'timeInSeconds' => '3661',
                'expectedTimeFormat' => '1h 1m 1s',
            ],
            '1 day 1 hour 1 minute and 1 second' => [
                'timeInSeconds' => '90061',
                'expectedTimeFormat' => '1d 1h 1m 1s',
            ],
        ];
    }
}
