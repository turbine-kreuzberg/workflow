<?php

namespace Turbine\Workflow\Transfers;

use Countable;
use Iterator;

class JiraWorklogEntryCollectionTransfer implements Iterator, Countable
{
    public function __construct(private array $issueCollection)
    {
    }

    public function current(): JiraWorklogEntryTransfer
    {
        return current($this->issueCollection);
    }

    public function next(): void
    {
        next($this->issueCollection);
    }

    public function key(): int|string|null
    {
        return key($this->issueCollection);
    }

    public function valid(): bool
    {
        return key($this->issueCollection) !== null;
    }

    public function rewind(): void
    {
        reset($this->issueCollection);
    }

    public function count(): int
    {
        return count($this->issueCollection);
    }
}
