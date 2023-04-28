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

namespace Mosparo\MosparoBundle\Tests\DependencyInjection;

use Mosparo\MosparoBundle\DependencyInjection\Compiler\ResourceCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\TwigBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 *
 * @coversNothing
 */
class ResourceCompilerPassTest extends TestCase
{
    public function testProcess()
    {
        $container = $this->getContainerBuilder();

        self::assertSame(['form_div_layout.html.twig'], $container->getParameter('twig.form.resources'));

        (new ResourceCompilerPass())->process($container);

        self::assertSame(['@Mosparo/mosparo_widget.html.twig', 'form_div_layout.html.twig'], $container->getParameter('twig.form.resources'));
    }

    private function getContainerBuilder(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $input = [
            'form_themes' => ['form_div_layout.html.twig'],
        ];

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [$input]);
        $container->setParameter('twig.form.resources', $config['form_themes']);

        return $container;
    }
}
