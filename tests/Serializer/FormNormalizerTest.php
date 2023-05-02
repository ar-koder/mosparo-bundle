<?php

/**
 * @package   MosparoBundle
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 * @link      https://github.com/arnaud-ritti/mosparo-bundle
 */

declare(strict_types=1);

namespace Mosparo\MosparoBundle\Tests\Serializer;

use Mosparo\MosparoBundle\Event\FilterFieldTypesEvent;
use Mosparo\MosparoBundle\Form\Type\MosparoType;
use Mosparo\MosparoBundle\Serializer\FormNormalizer;
use Mosparo\MosparoBundle\Tests\Traits\FormTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * @internal
 */
class FormNormalizerTest extends TestCase
{
    use FormTrait;

    private FormNormalizer $normalizer;

    protected EventDispatcherInterface $dispatcher;

    protected function setUp(): void
    {
        $this->setUpForm();
        $this->dispatcher = $this->createMock(EventDispatcher::class);
        $this->normalizer = new FormNormalizer($this->dispatcher);
    }

    public function testSupportedTypes(): void
    {
        $expected = [
            FormInterface::class => false,
        ];

        self::assertSame($expected, $this->normalizer->getSupportedTypes(null));
    }

    public function testCacheableSupportsMethod(): void
    {
        self::assertTrue($this->normalizer->hasCacheableSupportsMethod());
    }

    public function testSupportsNormalizationWithWrongClass(): void
    {
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testNormalizationWithWrongClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The object must be an instance of "Symfony\Component\Form\FormInterface".');
        $this->normalizer->normalize(new \stdClass());
    }

    public function testSupportsNormalizationWithNotSubmittedForm(): void
    {
        $form = $this->getCompoundForm([])
            ->add('name', TextType::class)
        ;

        self::assertFalse($this->normalizer->supportsNormalization($form));
    }

    public function testSupportsNormalizationWithValidForm(): void
    {
        $form = $this->getCompoundForm([])
            ->add('name', TextType::class)
            ->add('mosparo', MosparoType::class)
            ->submit(['name' => 'John Example', 'submit' => ''])
        ;

        self::assertTrue($this->normalizer->supportsNormalization($form));
    }

    /**
     * @throws ExceptionInterface
     */
    public function testNormalize(): void
    {
        $expected = [
            'formData' => [
                'name[name]' => 'John Example',
            ],
            'requiredFields' => [
                'name[name]',
            ],
            'verifiableFields' => [
                'name[name]',
            ],
        ];

        $form = $this->getCompoundForm([])
            ->add('name', TextType::class)
            ->add('mosparo', MosparoType::class)
            ->submit(['name' => 'John Example', 'submit' => ''])
        ;

        $this->assertEquals($expected, $this->normalizer->normalize($form));
    }

    public function testNormalizeWithTypeOverride(): void
    {
        $expected = [
            'formData' => [
                'name[name]' => 'John Example',
                'name[password]' => 'password',
                'name[optional]' => null,
            ],
            'requiredFields' => [
                'name[name]',
                'name[password]',
            ],
            'verifiableFields' => [
                'name[name]',
                'name[password]',
                'name[optional]',
            ],
        ];

        $form = $this->getCompoundForm([])
            ->add('name', TextType::class)
            ->add('password', PasswordType::class)
            ->add('optional', TextType::class, [
                'required' => false,
            ])
            ->add('mosparo', MosparoType::class)
            ->submit([
                'name' => 'John Example',
                'password' => 'password',
                'submit' => '',
            ])
        ;

        $this->dispatcher
            ->expects($this->once())
            ->method('hasListeners')
            ->willReturn(true)
        ;

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (FilterFieldTypesEvent $event): FilterFieldTypesEvent {
                $event->setIgnoredFieldTypes(array_diff($event->getIgnoredFieldTypes(), [PasswordType::class]));
                $event->setVerifiableFieldTypes(array_merge($event->getVerifiableFieldTypes(), [PasswordType::class]));

                return $event;
            })
        ;

        $this->assertEquals($expected, $this->normalizer->normalize($form));
    }

    /**
     * @throws ExceptionInterface
     */
    public function testNormalizeWithChildren(): void
    {
        $expected = [
            'formData' => [
                'name[name]' => 'John Example',
                'name[collection][0]' => 'Entry 1',
                'name[collection][1]' => 'Entry 2',
            ],
            'requiredFields' => [
                'name[name]',
                'name[collection][0]',
                'name[collection][1]',
            ],
            'verifiableFields' => [
                'name[name]',
                'name[collection][0]',
                'name[collection][1]',
            ],
        ];

        $form = $this->getCompoundForm([])
            ->add('name', TextType::class)
            ->add(
                'collection',
                CollectionType::class,
                [
                    'entry_type' => TextType::class,
                    'allow_add' => true,
                ]
            )
            ->add('mosparo', MosparoType::class)
            ->submit(
                [
                    'name' => 'John Example',
                    'collection' => [
                        0 => 'Entry 1',
                        1 => 'Entry 2',
                    ],
                    'submit' => '',
                ]
            )
        ;

        $this->assertEquals($expected, $this->normalizer->normalize($form));
    }
}
