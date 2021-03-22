<?php
declare(strict_types = 1);

namespace Turbine\Workflow\Workflow\Provider;

use Turbine\Workflow\Configuration;

class FastWorklogProvider
{
    public function __construct(
        public CommitMessageProvider $commitMessageProvider,
        public Configuration $configuration
    ) {

    }

    public function provide() : array
    {
        $lastCommitMessage = $this->commitMessageProvider->getLastCommitMessage();
        preg_match(
            "/(?'issueNumber'[A-Z]{3}-\d{1,5}).?\s(?'message'.*)$/i",
            $lastCommitMessage,
            $matches
        );

        if (count($matches) === 0) {
            return [null, null];
        }

        $issueNumber = strtoupper($matches['issueNumber']);
        $message = $matches['message'];

        return [$issueNumber, $message];
    }
}