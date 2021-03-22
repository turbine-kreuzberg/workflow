<?php

namespace Unit\Workflow\Validator;

use PHPUnit\Framework\TestCase;
use Turbine\Workflow\Workflow\Validator\BranchNameValidator;

class BranchNameValidatorTest extends TestCase
{
    /**
     * @dataProvider provideInvalidBranchNames
     */
    public function testInvalidBranchNameThrowsException(string $invalidBranchName): void
    {
        $branchNameValidator = new BranchNameValidator();
        $this->expectException(\UnexpectedValueException::class);
        $branchNameValidator->validate($invalidBranchName);
    }

    public function testBranchNameWithMoreThanFiftyCharactersThrowsException(): void
    {
        $branchNameValidator = new BranchNameValidator();
        $this->expectException(\LengthException::class);
        $branchNameValidator->validate(\str_repeat('a', 51));
    }

    public function testBranchNameWillBeReturned(): void
    {
        $branchNameValidator = new BranchNameValidator();
        $branchName = $branchNameValidator->validate(\str_repeat('A-branch-1', 5));

        self::assertEquals('A-branch-1A-branch-1A-branch-1A-branch-1A-branch-1', $branchName);
    }

    public function provideInvalidBranchNames(): array
    {
        return [
            'branch name with empty spaces' => ['branch name'],
            'branch name with umlauts' => ['bräönch'],
            'branch name with with punctuation marks' => ['branch?!.'],
        ];
    }
}