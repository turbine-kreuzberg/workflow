<?php

declare(strict_types=1);

namespace Turbine\Workflow\Workflow\Formatter;

use DateTime;

class HumanReadableDateIntervalFormatter
{
    public function format(int $timeInSeconds): string
    {
        $dateTime = new DateTime('@' . $timeInSeconds);
        $timeDiff = date_diff(new DateTime('@0'), $dateTime, true);

        $result = [];
        if ($timeDiff->d) {
            $result[] = $timeDiff->format('%dd');
        }
        if ($timeDiff->h) {
            $result[] = $timeDiff->format('%hh');
        }
        if ($timeDiff->i) {
            $result[] = $timeDiff->format('%im');
        }
        if ($timeDiff->s) {
            $result[] = $timeDiff->format('%ss');
        }

        return implode(' ', $result);
    }
}
