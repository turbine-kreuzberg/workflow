<?php

declare(strict_types=1);

namespace Turbine\Workflow\Console\SubConsole;

use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Workflow\Provider\FavouriteWorklogCommentChoicesProvider;
use Turbine\Workflow\Workflow\Provider\WorklogChoicesProvider;

class WorklogCommentConsole
{
    private const CUSTOM_INPUT = 'Custom input';

    public function __construct(
        private WorklogChoicesProvider $worklogChoicesProvider,
        private FavouriteWorklogCommentChoicesProvider $favouriteWorklogCommentChoicesProvider
    ) {
    }

    public function createWorklogComment(
        string $issueNumber,
        SymfonyStyle $inputOutputStyle
    ): string {
        $worklogChoices = array_unique(
            array_merge(
                $this->worklogChoicesProvider->provide($issueNumber),
                $this->favouriteWorklogCommentChoicesProvider->provide()
            )
        );

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
