<?php

namespace Turbine\Workflow\Transfers;

class MergeRequestParameterRequestTransfer
{
    public string $sourceBranch;
    public string $targetBranch;
    public string $state;
}
