<?php

declare(strict_types=1);

namespace Turbine\Workflow\Transfers;

class MergeRequestParameterRequestTransfer
{
    public string $sourceBranch;
    public string $targetBranch;
    public string $state;
}
