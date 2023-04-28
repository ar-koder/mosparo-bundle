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

namespace Mosparo\MosparoBundle\Tests\Traits;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\DataMapper\DataMapper;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\SubmitButtonBuilder;
use Symfony\Component\Validator\Validation;

trait FormTrait
{
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
        return $this->getBuilder('name', \is_object($data) ? $data::class : null, $options)
            ->setData($data)
            ->setCompound(true)
            ->setDataMapper(new DataMapper())
            ->getForm()
        ;
    }

    private function getSubmitButton($name = 'name', array $options = []): \Symfony\Component\Form\SubmitButton
    {
        return (new SubmitButtonBuilder($name, $options))->getForm();
    }

    private function setUpForm()
    {
        $this->dispatcher = new EventDispatcher();
        $this->factory = (new FormFactoryBuilder())
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory()
        ;
    }
}
