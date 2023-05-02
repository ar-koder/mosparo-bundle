<?php

/**
 * @package   MosparoBundle
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 * @link      https://github.com/arnaud-ritti/mosparo-bundle
 */

declare(strict_types=1);

namespace Mosparo\MosparoBundle\Tests\Form;

use Mosparo\MosparoBundle\DependencyInjection\MosparoExtension;
use Mosparo\MosparoBundle\Form\Type\MosparoType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Yaml\Parser;

/**
 * @internal
 */
class MosparoTypeTest extends TypeTestCase
{
    protected ?ContainerBuilder $configuration;

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->configuration = null;
        $this->factory = null;
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

    public const CONFIG = 'default';

    protected function getExtensions(): array
    {
        $this->createSampleConfiguration();
        $type = new MosparoType(self::CONFIG, $this->configuration->getParameterBag());

        return [
            new PreloadedExtension([$type], []),
        ];
    }

    public function testDefaultOptions(): void
    {
        $form = $this->factory->create(MosparoType::class);
        self::assertTrue($form->isSynchronized());
        $config = $this->getSampleConfig();
        $view = $form->createView();

        self::assertSame($config['projects']['default']['instance_url'], $view->vars['mosparo']['instance_url']);
        self::assertSame($config['projects']['default']['uuid'], $view->vars['mosparo']['uuid']);
        self::assertSame($config['projects']['default']['public_key'], $view->vars['mosparo']['public_key']);
        self::assertSame($config['projects']['default']['private_key'], $view->vars['mosparo']['private_key']);
        self::assertArrayHasKey('options', $view->vars['mosparo']);
        self::assertTrue($view->vars['mosparo']['enabled']);
    }

    public function testProjectOptions(): void
    {
        $form = $this->factory->create(MosparoType::class, [], [
            'project' => 'alt',
        ]);
        self::assertTrue($form->isSynchronized());
        $config = $this->getSampleConfig();
        $view = $form->createView();

        self::assertSame($config['projects']['alt']['instance_url'], $view->vars['mosparo']['instance_url']);
        self::assertSame($config['projects']['alt']['uuid'], $view->vars['mosparo']['uuid']);
        self::assertSame($config['projects']['alt']['public_key'], $view->vars['mosparo']['public_key']);
        self::assertSame($config['projects']['alt']['private_key'], $view->vars['mosparo']['private_key']);
        self::assertArrayHasKey('options', $view->vars['mosparo']);
        self::assertTrue($view->vars['mosparo']['enabled']);
    }
}
