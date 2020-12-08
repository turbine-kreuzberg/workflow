<?php
declare(strict_types = 1);


namespace Workflow;

class Configuration
{
    public const JIRA_PROJECT_KEY = "JIRA_PROJECT_KEY";

    public function getProjectKey(): string
    {
        try {
            return (string) getenv(self::JIRA_PROJECT_KEY);
        } catch (\Throwable $throwable) {
            throw new \RuntimeException('No project key set. Please see your ".env.dist" file how to create and use it.');
        }
    }
}