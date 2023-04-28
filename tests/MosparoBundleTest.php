<?php

/**
 * @package   MosparoBundle
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 * @link      https://github.com/arnaud-ritti/mosparo-bundle
 */

declare(strict_types=1);

namespace Mosparo\MosparoBundle\Tests;

use Mosparo\MosparoBundle\DependencyInjection\Compiler\ResourceCompilerPass;
use Mosparo\MosparoBundle\MosparoBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
class MosparoBundleTest extends TestCase
{
    public function testBuildCompilerPasses(): void
    {
        $container = new ContainerBuilder();
        $bundle = new MosparoBundle();
        $bundle->build($container);

        $config = $container->getCompilerPassConfig();
        $passes = $config->getBeforeOptimizationPasses();

        $foundResourceCompilerPass = false;

        foreach ($passes as $pass) {
            if ($pass instanceof ResourceCompilerPass) {
                $foundResourceCompilerPass = true;
            }
        }

        $this->assertTrue($foundResourceCompilerPass, 'ResourceCompilerPass was not found');
    }
}
