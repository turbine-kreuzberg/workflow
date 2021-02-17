<?php

namespace Turbine\Workflow\Client;

use GuzzleHttp\Exception\BadResponseException;
use Turbine\Workflow\Client\Http\GitlabHttpClient;
use Turbine\Workflow\Configuration;

class GitlabClient
{
    private const APPROVALS_BEFORE_MERGE_NONE = 0;
    private const APPROVALS_BEFORE_MERGE_DEFAULT = 2;

    public function __construct(
        private GitlabHttpClient $gitlabHttpClient,
        private Configuration $configuration
    ) {

    }

    public function createMergeRequest(array $mergeRequestData): string
    {
        $mergeRequestUrl = $this->getProjectUrl() . 'merge_requests';

        $sourceBranch = $mergeRequestData['source_branch'];
        $targetBranch = $mergeRequestData['target_branch'];

        $mergeRequestData['approvals_before_merge'] = $this->getApprovalBeforeMerge($sourceBranch, $targetBranch);
        $mergeRequestData['remove_source_branch'] = $this->shouldRemoveSourceBranch($sourceBranch);

        $gitlabResponse = $this->gitlabHttpClient->post($mergeRequestUrl, $mergeRequestData);

        return $gitlabResponse['web_url'];
    }

    private function getProjectUrl(): string
    {
        return $this->configuration->get(Configuration::GITLAB_API_URL)
            . 'projects/'
            . urlencode($this->configuration->get(Configuration::REPOSITORY))
            . '/';
    }

    private function getApprovalBeforeMerge(string $sourceBranch, string $targetBranch): int
    {
        if ($this->isDevelopmentBranchToDeploymentBranchMergeRequest($sourceBranch, $targetBranch)) {
            return self::APPROVALS_BEFORE_MERGE_NONE;
        }

        return self::APPROVALS_BEFORE_MERGE_DEFAULT;
    }

    private function shouldRemoveSourceBranch(string $sourceBranch): bool
    {
        if (!in_array($sourceBranch, $this->configuration->getNonRemovableBranches(), true)) {
            return true;
        }

        return false;
    }

    private function isDevelopmentBranchToDeploymentBranchMergeRequest(string $sourceBranch, string $targetBranch): bool
    {
        return $sourceBranch === $this->configuration->get(Configuration::BRANCH_DEVELOPMENT) &&
            $targetBranch === $this->configuration->get(Configuration::BRANCH_DEPLOYMENT);
    }
}
