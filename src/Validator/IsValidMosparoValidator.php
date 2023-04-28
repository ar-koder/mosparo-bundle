<?php

/**
 * @package   MosparoBundle
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 * @link      https://github.com/arnaud-ritti/mosparo-bundle
 */

declare(strict_types=1);

/**
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 *
 * @see      https://github.com/arnaud-ritti/mosparo-bundle
 */

namespace Mosparo\MosparoBundle\Validator;

use Mosparo\MosparoBundle\Serializer\FormNormalizer;
use Mosparo\MosparoBundle\Services\MosparoClient;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsValidMosparoValidator extends ConstraintValidator
{
    public function __construct(
        private MosparoClient $client,
        private RequestStack $requestStack,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsValidMosparo) {
            throw new UnexpectedTypeException($constraint, IsValidMosparo::class);
        }

        try {
            $request = $this->requestStack->getMainRequest();
            $mosparoSubmitToken = $request?->get('_mosparo_submitToken');
            $mosparoValidationToken = $request?->get('_mosparo_validationToken');

            if (empty($mosparoSubmitToken) || empty($mosparoValidationToken)) {
                $this->context->buildViolation($constraint::INVALID_TOKEN)
                    ->addViolation()
                ;

                return;
            }

            $form = $this->context->getRoot();
            if (!$form instanceof Form) {
                throw new UnexpectedTypeException($form, Form::class);
            }

            $formData = (new FormNormalizer())->normalize($form);
            $result = $this->client->verifySubmission($formData, $mosparoSubmitToken, $mosparoValidationToken);
            if (!$result->isSubmittable()) {
                if (\count($result->getIssues()) > 0) {
                    foreach ($result->getIssues() as $issue) {
                        if (!empty($issue['message'])) {
                            $this->context->buildViolation($issue['message'])
                                ->addViolation()
                            ;
                        }
                    }

                    return;
                }
                $this->context->buildViolation($constraint::VERIFICATION_FAILED)
                    ->addViolation()
                ;
            }
        } catch (\Exception|ExceptionInterface) {
            $this->context->buildViolation($constraint::ERROR)
                ->addViolation()
            ;
        }
    }
}
