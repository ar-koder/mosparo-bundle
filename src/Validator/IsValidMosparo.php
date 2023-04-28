<?php

/**
 * @package   MosparoBundle
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 * @link      https://github.com/arnaud-ritti/mosparo-bundle
 */

declare(strict_types=1);

namespace Mosparo\MosparoBundle\Validator;

use Symfony\Component\Validator\Constraint;

class IsValidMosparo extends Constraint
{
    public const VERIFICATION_FAILED = 'Verification failed which means the form contains spam.';
    public const INVALID_TOKEN = 'Submit or validation token not available.';
    public const ERROR = 'An error occurred while sending the request for validation.';
}
