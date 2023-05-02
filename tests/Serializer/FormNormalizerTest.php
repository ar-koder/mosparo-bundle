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

use Mosparo\MosparoBundle\Form\Type\MosparoType;
use Mosparo\MosparoBundle\Serializer\FormNormalizer;
use Mosparo\MosparoBundle\Tests\Traits\FormTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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

    protected function setUp(): void
    {
        $this->setUpForm();
        $this->normalizer = new FormNormalizer();
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
            'name[name]' => 'John Example',
        ];

        $form = $this->getCompoundForm([])
            ->add('name', TextType::class)
            ->add('mosparo', MosparoType::class)
            ->submit(['name' => 'John Example', 'submit' => ''])
        ;

        $this->assertEquals($expected, $this->normalizer->normalize($form));
    }

    /**
     * @throws ExceptionInterface
     */
    public function testNormalizeWithChildren(): void
    {
        $expected = [
            'name[name]' => 'John Example',
            'name[collection][0]' => 'Entry 1',
            'name[collection][1]' => 'Entry 2',
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
