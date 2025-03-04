<?php

/**
 * @package   MosparoBundle
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 * @link      https://github.com/arnaud-ritti/mosparo-bundle
 */

declare(strict_types=1);

namespace Mosparo\MosparoBundle\Serializer;

use Mosparo\MosparoBundle\Event\FilterFieldTypesEvent;
use Mosparo\MosparoBundle\Form\Type\MosparoType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Button;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FormNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher
    ) {
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        if (!$object instanceof FormInterface) {
            throw new \InvalidArgumentException('The object must be an instance of "Symfony\Component\Form\FormInterface".');
        }

        return $this->convertFormToArray($object);
    }

    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof FormInterface && $data->isSubmitted();
    }

    private function convertFormToArray(FormInterface $form): array
    {
        $ignoredFieldTypes = [
            MosparoType::class,
            RadioType::class,
            CheckboxType::class,
            HiddenType::class,
            FileType::class,
            ButtonType::class,
            SubmitType::class,
            ResetType::class,
            PasswordType::class,
        ];
        $verifiableFieldTypes = [
            TextType::class,
            TextareaType::class,
            EmailType::class,
            UrlType::class,
            TelType::class,
            NumberType::class,
            MoneyType::class,
            PercentType::class,
        ];

        if ($this->dispatcher->hasListeners(FilterFieldTypesEvent::class)) {
            $filterFieldTypesEvent = new FilterFieldTypesEvent($ignoredFieldTypes, $verifiableFieldTypes);
            $filterFieldTypesEvent = $this->dispatcher->dispatch($filterFieldTypesEvent);

            $ignoredFieldTypes = $filterFieldTypesEvent->getIgnoredFieldTypes();
            $verifiableFieldTypes = $filterFieldTypesEvent->getVerifiableFieldTypes();
        }

        $ignoredFieldTypes = array_merge($ignoredFieldTypes, [
            SubmitButton::class,
            Button::class,
        ]);

        $data = [];
        $this->parseFormData($form, $data, $ignoredFieldTypes, $verifiableFieldTypes);

        return $data;
    }

    private function parseFormData(FormInterface $form, array &$data, array $ignoredFieldTypes = [], array $verifiableFieldTypes = []): void
    {
        if ($form->count() > 0) {
            foreach ($form->all() as $field) {
                $this->parseFormData($field, $data, $ignoredFieldTypes, $verifiableFieldTypes);
            }

            return;
        }

        if (!$form->isRoot()) {
            if (
                \in_array(\get_class($form), $ignoredFieldTypes, true)
                || \in_array(\get_class($form->getConfig()->getType()), $ignoredFieldTypes, true)
                || \in_array(\get_class($form->getConfig()->getType()->getInnerType()), $ignoredFieldTypes, true)
            ) {
                return;
            }

            $key = $this->getKeyForField($form);
            $data['formData'][$key] = $form->getData();

            if ($form->isRequired()) {
                $data['requiredFields'][] = $key;
            }

            if (
                \in_array(\get_class($form), $verifiableFieldTypes, true)
                || \in_array(\get_class($form->getConfig()->getType()), $verifiableFieldTypes, true)
                || \in_array(\get_class($form->getConfig()->getType()->getInnerType()), $verifiableFieldTypes, true)
            ) {
                $data['verifiableFields'][] = $key;
            }
        }
    }

    private function getKeyForField(FormInterface $field): string
    {
        $parent = null;
        if ($field->getParent()) {
            $parent = $this->getKeyForField($field->getParent());
        }

        $name = (string) $field->getName();
        if ($parent) {
            $key = sprintf('%s[%s]', $parent, $name);
        } else {
            $key = $name;
        }

        return $key;
    }

    /**
     * @deprecated since Symfony 6.3, use "getSupportedTypes()" instead
     */
    public function hasCacheableSupportsMethod(): bool
    {
        trigger_deprecation('symfony/serializer', '6.3', 'The "%s()" method is deprecated, use "getSupportedTypes()" instead.', __METHOD__);

        return true;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            FormInterface::class => false,
        ];
    }
}
