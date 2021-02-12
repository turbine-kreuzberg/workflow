<?php

namespace Turbine\Workflow\Workflow;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Turbine\Workflow\Client\ClientFactory;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Workflow\Jira\IssueCreator;
use Turbine\Workflow\Workflow\Jira\IssueReader;
use Turbine\Workflow\Workflow\Jira\IssueUpdater;
use Turbine\Workflow\Workflow\Model\MergeRequestCreator;
use Turbine\Workflow\Workflow\Model\WorkOnTicket;
use Turbine\Workflow\Workflow\Provider\CommitMessageProvider;
use Turbine\Workflow\Workflow\Provider\FastWorklogProvider;
use Turbine\Workflow\Workflow\Provider\FavouriteTicketChoicesProvider;
use Turbine\Workflow\Workflow\Provider\WorklogChoicesProvider;
use Turbine\Workflow\Workflow\Validator\BranchNameValidator;

class WorkflowFactory
{
    private ?Configuration $configuration = null;

    private ?ClientFactory $clientFactory = null;

    private ?IssueReader $issueReader = null;

    private ?IssueUpdater $issueUpdater = null;

    public function createSymfonyStyle(InputInterface $input, OutputInterface $output): SymfonyStyle
    {
        return new SymfonyStyle($input, $output);
    }

    public function getTicketIdProvider(): TicketIdProvider
    {
        return new TicketIdProvider(
            $this->getClientFactory()->getGitClient(),
            $this->getTicketIdentifier()
        );
    }

    public function getTicketIdentifier(): TicketIdentifier
    {
        return new TicketIdentifier($this->createConfiguration());
    }

    public function createJiraIssueReader(): IssueReader
    {
        if ($this->issueReader === null) {
            $this->issueReader = new IssueReader(
                $this->getClientFactory()->getJiraClient(),
                $this->createConfiguration()
            );
        }

        return $this->issueReader;
    }

    public function createJiraIssueUpdater(): IssueUpdater
    {
        if ($this->issueUpdater === null) {
            $this->issueUpdater = new IssueUpdater(
                $this->getClientFactory()->getJiraClient()
            );
        }

        return $this->issueUpdater;
    }

    public function createFavouriteTicketChoicesProvider(): FavouriteTicketChoicesProvider
    {
        return new FavouriteTicketChoicesProvider(
            $this->createConfiguration(),
            $this->createJiraIssueReader()
        );
    }

    public function createJiraIssueCreator(): IssueCreator
    {
        return new IssueCreator($this->getClientFactory()->getJiraClient(), $this->createConfiguration());
    }

    public function createWorklogChoiceProvider(): WorklogChoicesProvider
    {
        return new WorklogChoicesProvider(
            $this->createJiraIssueReader(),
            $this->getCommitMessageProvider()
        );
    }

    public function createWorkOnTicket(): WorkOnTicket
    {
        return new WorkOnTicket(
            $this->getClientFactory()->getJiraClient(),
            $this->getClientFactory()->getGitClient(),
            $this->createConfiguration(),
            $this->createJiraIssueUpdater()
        );
    }

    public function createFastWorklogProvider() : FastWorklogProvider
    {
        return new FastWorklogProvider($this->getCommitMessageProvider(), $this->createConfiguration());
    }

    public function createMergeRequestCreator(): MergeRequestCreator
    {
        return new MergeRequestCreator(
            $this->getClientFactory()->getGitLabClient(),
            $this->getClientFactory()->getJiraClient(),
            $this->getClientFactory()->getGitClient(),
            $this->createConfiguration(),
            $this->getTicketIdProvider(),
            $this->createJiraIssueUpdater()
        );
    }

    public function createBranchNameValidator(): BranchNameValidator
    {
        return new BranchNameValidator();
    }

    private function getClientFactory(): ClientFactory
    {
        return new ClientFactory();
    }

    private function createConfiguration(): Configuration
    {
        if ($this->configuration === null) {
            $this->configuration = new Configuration();
        }

        return $this->configuration;
    }

    private function getCommitMessageProvider(): CommitMessageProvider
    {
        return new CommitMessageProvider($this->getClientFactory()->getGitClient());
    }
}
