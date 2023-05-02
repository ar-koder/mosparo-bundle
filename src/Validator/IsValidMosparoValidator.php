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

use Mosparo\MosparoBundle\Serializer\FormNormalizer;
use Mosparo\MosparoBundle\Services\MosparoClient;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsValidMosparoValidator extends ConstraintValidator
{
    public function __construct(
        private RequestStack $requestStack,
        private ParameterBagInterface $parameters,
        private bool $enabled = true,
    ) {
    }

    public function getClient(string $project = 'default'): MosparoClient
    {
        $host = $this->parameters->get(sprintf('mosparo.%s.%s', $project, 'instance_url'));
        $publicKey = $this->parameters->get(sprintf('mosparo.%s.%s', $project, 'public_key'));
        $privateKey = $this->parameters->get(sprintf('mosparo.%s.%s', $project, 'private_key'));
        $verifySsl = $this->parameters->get(sprintf('mosparo.%s.%s', $project, 'verify_ssl'));

        return MosparoClient::make($host, $publicKey, $privateKey, $verifySsl);
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsValidMosparo) {
            throw new UnexpectedTypeException($constraint, IsValidMosparo::class);
        }

        if (!$this->enabled) {
            return;
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
            $field = $form->get($this->context->getPropertyPath());
            $project = $field->getConfig()
                ->getOption('project', $this->parameters->get('mosparo.default_project'))
            ;
            $formData = (new FormNormalizer())->normalize($form);
            $result = $this
                ->getClient($project)
                ->verifySubmission($formData, $mosparoSubmitToken, $mosparoValidationToken)
            ;

            //            // Confirm that all required fields were verified
            //            $verifiedFields = array_keys($result->getVerifiedFields());
            //            $fieldDifference = array_diff($requiredFields, $verifiedFields);
            //            $verifiableFieldDifference = array_diff($verifiableFields, $verifiedFields);
            //
            //            if ($result->isSubmittable() && empty($fieldDifference) && empty($verifiableFieldDifference)) {
            //                // Remove the mosparo field from the record since it is not needed in the process
            //                $record->remove_field($mosparoField['id']);
            //                return;
            //            }

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
        } catch (\Exception|ExceptionInterface $e) {
            $this->context->buildViolation($constraint::ERROR)
                ->addViolation()
            ;
        }
    }
}
