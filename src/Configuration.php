<?php
declare(strict_types = 1);

namespace Workflow;

class Configuration
{
    public const JIRA_PROJECT_KEY = "JIRA_PROJECT_KEY";
    public const JIRA_FAVOURITE_TICKETS = 'JIRA_FAVOURITE_TICKETS';
    public const JIRA_USERNAME = 'JIRA_USERNAME';
    public const JIRA_PASSWORD = 'JIRA_PASSWORD';
    public const PROJECT_NAME = 'JIRA_PROJECT_NAME';
    public const BOARD_ID = 'JIRA_BOARD_ID';
    public const BRANCH_DEVELOPMENT = 'BRANCH_DEVELOPMENT';
    public const BRANCH_DEPLOYMENT = 'BRANCH_DEPLOYMENT';
    public const REPOSITORY = 'REPOSITORY';
    public const GITLAB_API_URL = 'GITLAB_API_URL';


    public function getConfiguration(string $key): string
    {
        $configurationValue = (string)getenv($key);
        if (!$configurationValue) {
            $this->throwException($key);
        }

        return $configurationValue;
    }

    public function getNonRemovableBranches(): array
    {
        return [
            $this->getConfiguration(self::BRANCH_DEVELOPMENT),
            $this->getConfiguration(self::BRANCH_DEPLOYMENT),
        ];
    }

    private function throwException(string $key): void
    {
        $errorMessage = sprintf(
            'Configuration with %s was not found. Please check your ".env.dist" file how to create and use it',
            $key
        );

        throw new \RuntimeException($errorMessage);
    }
}