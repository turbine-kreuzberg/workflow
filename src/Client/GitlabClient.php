<?php

namespace Workflow\Client;

use Exception;
use GuzzleHttp\Exception\BadResponseException;
use Workflow\Client\Http\GitlabHttpClient;
use Workflow\Configuration;
use Workflow\Transfers\MergeRequestParameterRequestTransfer;

class GitlabClient
{
    private const APPROVALS_BEFORE_MERGE_NONE = 0;
    private const APPROVALS_BEFORE_MERGE_DEFAULT = 2;

    public function __construct(
        private GitlabHttpClient $gitlabHttpClient,
        private Configuration $configuration
    ) {

    }

    public static function requiredEnvironmentVariables(): array
    {
        return [GitlabHttpClient::PERSONAL_ACCESS_TOKEN];
    }

    public function setAccessLevel(string $branchName, string $mergeAccessLevel, string $pushAccessLevel): void
    {
        $protectBranchUrl = $this->getProjectUrl() . 'protected_branches';

        $postData = [
            'name' => $branchName,
            'push_access_level' => $pushAccessLevel,
            'merge_access_level' => $mergeAccessLevel,
        ];

        try {
            $this->gitlabHttpClient->delete($protectBranchUrl . '/' . $branchName);
        } catch (BadResponseException $exception) {
            /**
             * If the branch is not protected we would get an error while trying to delete the protection.
             * We don't need to do anything in that case.
             */
        }

        $this->gitlabHttpClient->post($protectBranchUrl, $postData);
    }

    public function isBranchProtected(string $branchName): bool
    {
        $branchUrl = $this->getProjectUrl() . 'repository/branches/' . $branchName;

        $response = $this->gitlabHttpClient->get($branchUrl);

        return !$response['developers_can_merge'];
    }

    public function createMergeRequest(array $mergeRequestData): string
    {
        $mergeRequestUrl = $this->getProjectUrl() . 'merge_requests';

        $sourceBranch = $mergeRequestData['source_branch'];
        $targetBranch = $mergeRequestData['target_branch'];

        $mergeRequestData['approvals_before_merge'] = $this->getApprovalBeforeMerge($sourceBranch, $targetBranch);
        $mergeRequestData['remove_source_branch'] = $this->shouldRemoveSourceBranch($sourceBranch);

        try {
            $gitlabResponse = $this->gitlabHttpClient->post($mergeRequestUrl, $mergeRequestData);
        } catch (BadResponseException $exception) {
            if ($exception->getResponse()->getStatusCode() === 409) {
                $gitlabResponse = $this->gitlabHttpClient->get($mergeRequestUrl, ['query' => $mergeRequestData]);

                return $gitlabResponse[0]['web_url'];
            }

            throw $exception;
        }

        return $gitlabResponse['web_url'];
    }

    public function getCurrentBranchRevision(string $branchName): string
    {
        $commitUrls = $this->getProjectUrl() . 'repository/commits?ref_name=' . $branchName;

        $response = $this->gitlabHttpClient->get($commitUrls);

        return $response[0]['id'];
    }

    private function getProjectUrl(): string
    {
        return $this->configuration->getConfiguration(Configuration::GITLAB_API_URL)
            . 'projects/'
            . urlencode($this->configuration->getConfiguration(Configuration::REPOSITORY))
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
        return $sourceBranch === $this->configuration->getConfiguration(Configuration::BRANCH_DEVELOPMENT) &&
            $targetBranch === $this->configuration->getConfiguration(Configuration::BRANCH_DEPLOYMENT);
    }

    public function getMergeRequestId(MergeRequestParameterRequestTransfer $mergeRequestParameterRequestTransfer): int
    {
        $mergeRequestUrlParameters = [
            'source_branch' => $mergeRequestParameterRequestTransfer->getSourceBranch(),
            'target_branch' => $mergeRequestParameterRequestTransfer->getTargetBranch(),
            'state' => $mergeRequestParameterRequestTransfer->getState(),
        ];

        $mergeRequestUrl = $this->getProjectUrl() . 'merge_requests/?' . http_build_query($mergeRequestUrlParameters);
        $gitlabResponseArray = $this->gitlabHttpClient->get($mergeRequestUrl);

        $mergeRequestId = $gitlabResponseArray[0]['iid'];
        if ($mergeRequestId === null) {
            throw new Exception(
                'Merge request from ' .
                $mergeRequestParameterRequestTransfer->getSourceBranch() . ' to ' .
                $mergeRequestParameterRequestTransfer->getTargetBranch() . ' not found.'
            );
        }

        return $mergeRequestId;
    }


    /**
     * @param int $mergeRequestId
     *
     * @return string[]
     */
    public function getMergeRequestChangedFiles(int $mergeRequestId): array
    {
        $mergeRequestChangesUrl = $this->getProjectUrl() . 'merge_requests/' . $mergeRequestId . '/changes';
        $response = $this->gitlabHttpClient->get($mergeRequestChangesUrl);
        $changes = $response['changes'];

        $changedFiles = [];
        foreach ($changes as $change) {
            $changedFiles[] = $change['new_path'];
        }

        return $changedFiles;
    }
}
