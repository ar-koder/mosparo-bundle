<?php

/**
 * @package   MosparoBundle
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 * @link      https://github.com/arnaud-ritti/mosparo-bundle
 */

declare(strict_types=1);

namespace Mosparo\MosparoBundle\Form\Type;

use Mosparo\MosparoBundle\Validator\IsValidMosparo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MosparoType extends AbstractType
{
    public function __construct(
        private string $instanceUrl,
        private string $uuid,
        private string $publicKey,
        private string $privateKey,
        private bool $enabled = true,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'error_bubbling' => false,
                'compound' => false,
                'invalid_message' => 'The mosparo field is invalid.',
                'constraints' => [new IsValidMosparo()],
                'allowBrowserValidation' => false,
                'cssResourceUrl' => '',
                'designMode' => false,
                'inputFieldSelector' => '[name]:not(.mosparo__ignored-field)',
                'loadCssResource' => true,
                'requestSubmitTokenOnInit' => true,
            ]
        );

        $resolver->setAllowedTypes('allowBrowserValidation', 'bool');
        $resolver->setAllowedTypes('designMode', 'bool');
        $resolver->setAllowedTypes('loadCssResource', 'bool');
        $resolver->setAllowedTypes('requestSubmitTokenOnInit', 'bool');
        $resolver->setAllowedTypes('inputFieldSelector', 'string');
        $resolver->setAllowedTypes('cssResourceUrl', 'string');
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['mosparo'] = [
            'enabled' => $this->enabled,
            'instance_url' => $this->instanceUrl,
            'uuid' => $this->uuid,
            'public_key' => $this->publicKey,
            'private_key' => $this->privateKey,
            'options' => [
                'allowBrowserValidation' => $options['allowBrowserValidation'],
                'cssResourceUrl' => $options['cssResourceUrl'],
                'designMode' => $options['designMode'],
                'inputFieldSelector' => $options['inputFieldSelector'],
                'loadCssResource' => $options['loadCssResource'],
                'requestSubmitTokenOnInit' => $options['requestSubmitTokenOnInit'],
            ],
        ];
    }
}
