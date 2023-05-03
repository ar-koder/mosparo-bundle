<?php

/**
 * @package   MosparoBundle
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 * @link      https://github.com/arnaud-ritti/mosparo-bundle
 */

declare(strict_types=1);

namespace Mosparo\MosparoBundle\Tests\Traits;

use Mosparo\MosparoBundle\DependencyInjection\MosparoExtension;
use Mosparo\MosparoBundle\Form\Type\MosparoType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\DataMapper\DataMapper;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\SubmitButtonBuilder;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Yaml\Parser;

trait FormTrait
{
    protected ?ContainerBuilder $configuration;

    protected FormFactoryInterface $factory;
    protected FormBuilder $builder;
    protected EventDispatcherInterface $dispatcher;

    private function getBuilder(string $name = 'name', string $dataClass = null, array $options = []): FormBuilder
    {
        $options = array_replace(
            [
                'constraints' => [],
                'invalid_message_parameters' => [],
            ],
            $options
        );

        return new FormBuilder($name, $dataClass, $this->dispatcher, $this->factory, $options);
    }

    private function getCompoundForm($data, array $options = []): \Symfony\Component\Form\FormInterface
    {
        return $this->getBuilder('form', \is_object($data) ? $data::class : null, $options)
            ->setData($data)
            ->setCompound(true)
            ->setDataMapper(new DataMapper())
            ->add(new SubmitButtonBuilder('submit'))
            ->getForm()
        ;
    }

    private function getSubmitButton($name = 'name', array $options = []): \Symfony\Component\Form\SubmitButton
    {
        return (new SubmitButtonBuilder($name, $options))->getForm();
    }

    private function setUpForm()
    {
        $this->createSampleConfiguration();
        $this->dispatcher = new EventDispatcher();
        $this->factory = (new FormFactoryBuilder())
            ->addExtension(new PreloadedExtension([
                new MosparoType('default', $this->configuration->getParameterBag()),
            ], []))
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory()
        ;
    }

    private function tearDownForm()
    {
        $this->configuration = null;
    }

    protected function createSampleConfiguration(): void
    {
        $this->configuration = new ContainerBuilder();
        $loader = new MosparoExtension();
        $config = $this->getSampleConfig();
        $loader->load([$config], $this->configuration);
        $this->assertInstanceOf(ContainerBuilder::class, $this->configuration);
    }

    protected function getSampleConfig()
    {
        $yaml = <<<'EOF'
default_project: default
projects:
    default:
        instance_url: https://example.com
        uuid: c75cde8e-681e-4618-b4c9-02f0636bdf25
        public_key: xo0EZEo5eAEEAMVGSnNwqDdaMTZLxY
        private_key: xcFGBGRKOXgBBADFRkpzcKg3WjE2S8WPpXAVNdU
    alt:
        instance_url: https://example.com
        uuid: df056fb7-04a1-4d12-abde-70b75ece3847
        public_key: xo0EZEo5eAEEAMVGSnNwqDdaMTZLxY
        private_key: xcFGBGRKOXgBBADFRkpzcKg3WjE2S8WPpXAVNdU
EOF;

        return (new Parser())->parse($yaml);
    }
}
