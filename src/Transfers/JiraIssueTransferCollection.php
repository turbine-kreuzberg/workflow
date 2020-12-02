<?php

namespace Workflow\Transfers;

use Countable;
use Iterator;

class JiraIssueTransferCollection implements Iterator, Countable
{
    /**
     * @var array
     */
    private $issueCollection;

    /**
     * @param \Workflow\Transfers\JiraIssueTransfer[] $issues
     */
    public function __construct(array $issues)
    {
        $this->issueCollection = $issues;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->issueCollection);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        next($this->issueCollection);
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return key($this->issueCollection);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return key($this->issueCollection) !== null;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->issueCollection);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->issueCollection);
    }
}
