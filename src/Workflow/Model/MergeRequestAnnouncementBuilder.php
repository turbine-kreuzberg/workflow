<?php

namespace Turbine\Workflow\Workflow\Model;

use Turbine\Workflow\Client\GitlabClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Exception\CommandNotApplicableForBranchException;
use Turbine\Workflow\Exception\MergeRequestDataNotFoundException;
use Turbine\Workflow\Workflow\Provider\BranchNameProvider;

class MergeRequestAnnouncementBuilder
{
    public function __construct(
        private BranchNameProvider $branchNameProvider,
        private GitlabClient $gitlabClient,
        private Configuration $configuration
    ) {
    }

    public function getAnnouncementMessageForSlack(): string
    {
        $mergeRequestData = $this->getMergeRequestDataForCurrentBranch();

        return $this->buildMessage($mergeRequestData[0]);
    }

    private function getMergeRequestDataForCurrentBranch(): array
    {
        $currentBranchName = $this->branchNameProvider->getCurrentBranchName();

        if (in_array($currentBranchName, $this->configuration->getNonRemovableBranches(), true)) {
            throw new CommandNotApplicableForBranchException(
                sprintf(
                    'This command is not applicable for branch "%s"',
                    $currentBranchName
                )
            );
        }

        $mergeRequestData = $this->gitlabClient->getMergeRequestData(
            [
                'source_branch' => $currentBranchName,
                'state' => 'opened',
            ]
        );

        if (empty($mergeRequestData)) {
            throw new MergeRequestDataNotFoundException(
                sprintf('Could not find any open merge request for current branch "%s"', $currentBranchName)
            );
        }

        return $mergeRequestData;
    }

    private function buildMessage(array $mergeRequestData): string
    {
        $mergeRequestTitle = $mergeRequestData['title'];
        $mergeRequestUrl = $mergeRequestData['web_url'];

        return sprintf(
            "<!channel> New MR\n*%s*\n%s",
            $mergeRequestTitle,
            $mergeRequestUrl
        );
    }
}
