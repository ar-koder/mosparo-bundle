<?php

namespace Mosparo\MosparoBundle\Serializer;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FormNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
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
        $data = [];
        foreach ($form->getData() as $key => $value) {
            $this->convertFormChildrenToArray($key, $value, $form->getName(), $data);
        }

        return $data;
    }

    private function convertFormChildrenToArray(string $key, mixed $value, string $ancestor, array &$data): void
    {
        $key = sprintf('%s[%s]', $ancestor, $key);
        if (is_iterable($value)) {
            foreach ($value as $k => $v) {
                $this->convertFormChildrenToArray($k, $v, $key, $data);
            }

            return;
        }

        $data[$key] = $value;
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
