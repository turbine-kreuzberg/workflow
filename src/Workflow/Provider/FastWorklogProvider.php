<?php
declare(strict_types = 1);


namespace Workflow\Workflow\Provider;


use Workflow\Configuration;
use Workflow\Workflow\Exception\MalformedCommitMessageException;

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
            "/(?'issueNumber'[A-Z]{3}-\d{1,5}) (?'message'.*)$/",
            $lastCommitMessage,
            $matches
        );

        if (!isset($matches['issueNumber']) || !isset($matches['message'])) {
            throw new MalformedCommitMessageException();
        }

        $issueNumber = $matches['issueNumber'];
        $message = $matches['message'];

        return [$issueNumber, $message];
    }
}