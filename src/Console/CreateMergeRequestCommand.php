<?php

namespace Workflow\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Workflow\Client\ClientFactory;
use Workflow\Configuration;
use Workflow\Workflow\WorkflowFactory;

class CreateMergeRequestCommand extends Command
{
    public const COMMAND_NAME = 'workflow:create-merge-request';
    public const SOURCE_BRANCH_NAME = 'sourceBranchName';
    public const TARGET_BRANCH_NAME = 'targetBranchName';
    private const STATUS_CODE_REVIEW = 'code review';

    public function __construct(
        ?string $name = null,
        private ClientFactory $clientFactory,
        private Configuration $configuration,
        private WorkflowFactory $workflowFactory
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Create merge request for current branch to develop.');
    }


    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $currentBranchName = $this->clientFactory->getGitClient()->getCurrentBranchName();

        $gitlabClient = $this->clientFactory->getGitlabClient();
        $currentIssue = $this->workflowFactory->getTicketIdProvider()->extractTicketIdFromCurrentBranch();
        $jiraClient = $this->clientFactory->getJiraClient();
        $jiraIssueTransfer = $jiraClient->getIssue($currentIssue);

        $description = $this->createDescription($currentIssue);

        $mergeRequestData = [
            'source_branch' => $currentBranchName,
            'target_branch' => $this->configuration->getConfiguration(Configuration::BRANCH_DEVELOPMENT),
            'title' => '[' . $jiraIssueTransfer->key . '] ' . $jiraIssueTransfer->summary,
            'description' => $description,
            'remove_source_branch' => true,
            'labels' => implode(',', $jiraIssueTransfer->labels),
            'approvals_before_merge' => 2,
        ];

        $mergeRequestUrl = $gitlabClient->createMergeRequest($mergeRequestData);
        $this->workflowFactory->createJiraIssueUpdater()->moveIssueToStatus(
            $jiraIssueTransfer->key,
            self::STATUS_CODE_REVIEW
        );

        $inputOutputStyle = new SymfonyStyle($input, $output);
        $inputOutputStyle->success(
            [
                'Created merge request: ' . $mergeRequestUrl,
                'The ticket was moved to code review.',
            ]
        );
    }

    private function createDescription(string $currentIssue): string
    {
        $gitLog = $this->clientFactory->getGitClient()->getGitLog();

        $description = '';
        $description .= $gitLog . PHP_EOL;
        $description .= 'Closes ' . $currentIssue . PHP_EOL;

        return $description;
    }
}