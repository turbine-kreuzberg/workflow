<?php

declare(strict_types=1);

namespace Turbine\Workflow\Workflow\Provider;

use Turbine\Workflow\Configuration;

class FavouriteWorklogCommentChoicesProvider
{
    public function __construct(private Configuration $configuration)
    {
    }

    public function provide(): array
    {
        $favouriteWorklogCommentsFromEnvironment = $this->configuration->get(
            Configuration::FAVOURITE_WORKLOG_COMMENTS
        );

        if (empty($favouriteWorklogCommentsFromEnvironment)) {
            return [];
        }

        return explode(',', $favouriteWorklogCommentsFromEnvironment);
    }
}
