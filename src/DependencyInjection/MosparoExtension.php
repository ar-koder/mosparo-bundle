<?php

/**
 * @package   MosparoBundle
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 * @link      https://github.com/arnaud-ritti/mosparo-bundle
 */

declare(strict_types=1);

namespace Mosparo\MosparoBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class MosparoExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config as $key => $value) {
            if ('projects' === $key) {
                foreach ($value as $project => $settings) {
                    foreach ($settings as $settingKey => $settingValue) {
                        $container->setParameter(sprintf('mosparo.%s.%s', $project, $settingKey), $settingValue);
                    }
                }
            } else {
                $container->setParameter(sprintf('mosparo.%s', $key), $value);
            }
        }

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yaml');
    }

    public function getAlias(): string
    {
        return 'mosparo';
    }
}
