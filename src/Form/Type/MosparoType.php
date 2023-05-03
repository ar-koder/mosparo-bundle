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

use Mosparo\ApiClient\Exception;
use Mosparo\MosparoBundle\Validator\IsValidMosparo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MosparoType extends AbstractType
{
    public function __construct(
        private string $config,
        private ParameterBagInterface $parameters,
        private bool $enabled = true
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
                'project' => $this->config,
                'allowBrowserValidation' => false,
                'cssResourceUrl' => '',
                'designMode' => false,
                'inputFieldSelector' => '[name]:not(.mosparo__ignored-field)',
                'loadCssResource' => true,
                'requestSubmitTokenOnInit' => true,
            ]
        );

        $resolver->setAllowedTypes('project', 'string');
        $resolver->setAllowedTypes('allowBrowserValidation', 'bool');
        $resolver->setAllowedTypes('designMode', 'bool');
        $resolver->setAllowedTypes('loadCssResource', 'bool');
        $resolver->setAllowedTypes('requestSubmitTokenOnInit', 'bool');
        $resolver->setAllowedTypes('inputFieldSelector', 'string');
        $resolver->setAllowedTypes('cssResourceUrl', 'string');
    }

    /**
     * @throws Exception
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $hostKey = sprintf('mosparo.%s.%s', $options['project'], 'instance_url');
        $host = $this->parameters->get($hostKey);
        if (false === filter_var($host, \FILTER_VALIDATE_URL)) {
            throw new Exception(sprintf('Please check your "%s". "%s" is not a valid URL', $hostKey, $host));
        }

        $uuidKey = sprintf('mosparo.%s.%s', $options['project'], 'uuid');
        $uuid = $this->parameters->get($uuidKey);
        if (false === Uuid::isValid($uuid)) {
            throw new Exception(sprintf('Please check your "%s". "%s" is not a valid UUID', $uuidKey, $uuid));
        }

        $view->vars['mosparo'] = [
            'enabled' => $this->enabled,
            'instance_url' => $host,
            'uuid' => $uuid,
            'public_key' => $this->parameters->get(sprintf('mosparo.%s.%s', $options['project'], 'public_key')),
            'private_key' => $this->parameters->get(sprintf('mosparo.%s.%s', $options['project'], 'private_key')),
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
