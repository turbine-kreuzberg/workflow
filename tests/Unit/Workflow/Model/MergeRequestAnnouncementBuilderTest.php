<?php

namespace Unit\Workflow\Model;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Client\GitlabClient;
use Turbine\Workflow\Client\SlackClient;
use Turbine\Workflow\Configuration;
use Turbine\Workflow\Exception\CommandNotApplicableForBranchException;
use Turbine\Workflow\Exception\MergeRequestDataNotFoundException;
use Turbine\Workflow\Workflow\Model\MergeRequestAnnouncementBuilder;
use Turbine\Workflow\Workflow\Model\SlackMessageSender;
use Turbine\Workflow\Workflow\Provider\BranchNameProvider;

class MergeRequestAnnouncementBuilderTest extends TestCase
{
    public function testGetAnnouncementMessageForSlack(): void
    {
        $branchName = 'current-branch';
        $branchNameProviderMock = $this->createMock(BranchNameProvider::class);
        $branchNameProviderMock->expects(self::once())->method('getCurrentBranchName')->willReturn($branchName);

        $gitlabClientMock = $this->createMock(GitlabClient::class);
        $gitlabClientMock
            ->expects(self::once())
            ->method('getMergeRequestData')
            ->with(['source_branch' => $branchName, 'state' => 'opened'])
            ->willReturn(
                [
                    [
                        'title' => 'mr title',
                        'web_url' => 'mr-url',
                    ]
                ]
            );

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())->method('getNonRemovableBranches')->willReturn(['main']);

        $mergeRequestAnnouncementBuilder = new MergeRequestAnnouncementBuilder(
            $branchNameProviderMock,
            $gitlabClientMock,
            $configurationMock
        );

        $message = $mergeRequestAnnouncementBuilder->getAnnouncementMessageForSlack();

        self::assertStringContainsString('mr title', $message);
        self::assertStringContainsString('mr-url', $message);
    }

    public function testGetAnnouncementMessageForSlackThrowsExceptionIfBranchIsNotApplicable(): void
    {
        $branchName = 'main';
        $branchNameProviderMock = $this->createMock(BranchNameProvider::class);
        $branchNameProviderMock->expects(self::once())->method('getCurrentBranchName')->willReturn($branchName);

        $gitlabClientMock = $this->createMock(GitlabClient::class);
        $gitlabClientMock
            ->expects(self::never())
            ->method('getMergeRequestData');

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())->method('getNonRemovableBranches')->willReturn(['main']);

        $mergeRequestAnnouncementBuilder = new MergeRequestAnnouncementBuilder(
            $branchNameProviderMock,
            $gitlabClientMock,
            $configurationMock
        );

        $this->expectException(CommandNotApplicableForBranchException::class);
        $this->expectExceptionMessage('This command is not applicable for branch "main"');

        $mergeRequestAnnouncementBuilder->getAnnouncementMessageForSlack();
    }

    public function testGetAnnouncementMessageForSlackThrowsExceptionIfNoMergeRequestFound(): void
    {
        $branchName = 'current-branch';
        $branchNameProviderMock = $this->createMock(BranchNameProvider::class);
        $branchNameProviderMock->expects(self::once())->method('getCurrentBranchName')->willReturn($branchName);

        $gitlabClientMock = $this->createMock(GitlabClient::class);
        $gitlabClientMock
            ->expects(self::once())
            ->method('getMergeRequestData')
            ->with(['source_branch' => $branchName, 'state' => 'opened'])
            ->willReturn([]);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())->method('getNonRemovableBranches')->willReturn(['main']);

        $mergeRequestAnnouncementBuilder = new MergeRequestAnnouncementBuilder(
            $branchNameProviderMock,
            $gitlabClientMock,
            $configurationMock
        );

        $this->expectException(MergeRequestDataNotFoundException::class);
        $this->expectExceptionMessage('Could not find any open merge request for current branch "current-branch"');

        $mergeRequestAnnouncementBuilder->getAnnouncementMessageForSlack();
    }
}
