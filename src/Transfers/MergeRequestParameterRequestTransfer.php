<?php

namespace Workflow\Transfers;

class MergeRequestParameterRequestTransfer
{
    /**
     * @var string
     */
    private $sourceBranch;

    /**
     * @var string
     */
    private $targetBranch;

    /**
     * @var string
     */
    private $state;

    /**
     * @return string
     */
    public function getSourceBranch(): string
    {
        return $this->sourceBranch;
    }

    /**
     * @param string $sourceBranch
     *
     * @return void
     */
    public function setSourceBranch(string $sourceBranch): void
    {
        $this->sourceBranch = $sourceBranch;
    }

    /**
     * @return string
     */
    public function getTargetBranch(): string
    {
        return $this->targetBranch;
    }

    /**
     * @param string $targetBranch
     *
     * @return void
     */
    public function setTargetBranch(string $targetBranch): void
    {
        $this->targetBranch = $targetBranch;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @return void
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }
}
