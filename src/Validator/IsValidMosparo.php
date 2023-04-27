<?php

namespace Mosparo\MosparoBundle\Validator;

use Symfony\Component\Validator\Constraint;

class IsValidMosparo extends Constraint
{
    public const VERIFICATION_FAILED = 'Verification failed which means the form contains spam.';
    public const INVALID_TOKEN = 'Submit or validation token not available.';
    public const ERROR = 'An error occurred while sending the request for validation.';
}
