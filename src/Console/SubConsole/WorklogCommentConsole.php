<?php

namespace Turbine\Workflow\Console\SubConsole;

use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Workflow\Provider\WorklogChoicesProvider;

class WorklogCommentConsole
{
    private const CUSTOM_INPUT = 'Custom input';

    public function __construct(private WorklogChoicesProvider $worklogChoicesProvider)
    {
    }

    public function createWorklogComment(
        string $issueNumber,
        SymfonyStyle $inputOutputStyle
    ): string {
        $worklogChoices = $this->worklogChoicesProvider->provide($issueNumber);

        $worklogChoices[] = self::CUSTOM_INPUT;

        $commentChoice = $inputOutputStyle->choice(
            'Choose your worklog comment',
            $worklogChoices,
            $worklogChoices[0]
        );

        $summary = $commentChoice;
        if ($commentChoice === self::CUSTOM_INPUT) {
            $summary = $inputOutputStyle->ask('What did you do');
        }

        return $summary;
    }
}
