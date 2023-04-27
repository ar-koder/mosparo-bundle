<?php

/**
 * @package   MosparoBundle
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 * @link      https://github.com/arnaud-ritti/mosparo-bundle
 */

declare(strict_types=1);

namespace Mosparo\MosparoBundle\Tests\DependencyInjection;

use Mosparo\MosparoBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * @internal
 */
class ConfigurationTest extends TestCase
{
    /**
     * @dataProvider dataForProcessedConfiguration
     *
     * @param mixed $configs
     * @param mixed $expectedConfig
     */
    public function testProcessedConfiguration($configs, $expectedConfig)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);
        self::assertSame($expectedConfig, $config);
    }

    public function dataForProcessedConfiguration()
    {
        return [
            [
                [
                    'mosparo' => [
                        'instance_url' => 'https://example.com',
                        'uuid' => 'c75cde8e-681e-4618-b4c9-02f0636bdf25',
                        'public_key' => 'xo0EZEo5eAEEAMVGSnNwqDdaMTZLxY',
                        'private_key' => 'xcFGBGRKOXgBBADFRkpzcKg3WjE2S8WPpXAVNdU',
                    ],
                ],
                [
                    'instance_url' => 'https://example.com',
                    'uuid' => 'c75cde8e-681e-4618-b4c9-02f0636bdf25',
                    'public_key' => 'xo0EZEo5eAEEAMVGSnNwqDdaMTZLxY',
                    'private_key' => 'xcFGBGRKOXgBBADFRkpzcKg3WjE2S8WPpXAVNdU',
                    'guzzle_options' => [],
                ],
            ],
            [
                [
                    'mosparo' => [
                        'instance_url' => 'https://example.com',
                        'uuid' => 'c75cde8e-681e-4618-b4c9-02f0636bdf25',
                        'public_key' => 'xo0EZEo5eAEEAMVGSnNwqDdaMTZLxY',
                        'private_key' => 'xcFGBGRKOXgBBADFRkpzcKg3WjE2S8WPpXAVNdU',
                        'guzzle_options' => [
                            ['option' => 'verify', 'value' => false],
                        ],
                    ],
                ],
                [
                    'instance_url' => 'https://example.com',
                    'uuid' => 'c75cde8e-681e-4618-b4c9-02f0636bdf25',
                    'public_key' => 'xo0EZEo5eAEEAMVGSnNwqDdaMTZLxY',
                    'private_key' => 'xcFGBGRKOXgBBADFRkpzcKg3WjE2S8WPpXAVNdU',
                    'guzzle_options' => [
                        'verify' => false,
                    ],
                ],
            ],
        ];
    }
}
