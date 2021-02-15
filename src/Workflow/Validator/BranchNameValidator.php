<?php

namespace Turbine\Workflow\Workflow\Validator;

use LengthException;
use UnexpectedValueException;

class BranchNameValidator
{

    public function validate(string $branchName): string
    {
        if (!preg_match('/^[a-z0-9-]+$/i', $branchName)) {
            throw new UnexpectedValueException(
                'Invalid branch name (permitted are only lower case characters, numbers and the dash).'
            );
        }

        if (strlen($branchName) > 50) {
            throw new LengthException('Invalid branch name (maximal 50 characters).');
        }

        return $branchName;
    }
}