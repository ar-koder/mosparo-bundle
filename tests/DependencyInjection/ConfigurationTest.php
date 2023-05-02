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
     */
    public function testProcessedConfiguration(array $configs, array $expectedConfig): void
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);
        self::assertEquals($expectedConfig, $config);
    }

    public function dataForProcessedConfiguration(): array
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
                    'enabled' => true,
                    'default_project' => 'default',
                    'projects' => [
                        'default' => [
                            'instance_url' => 'https://example.com',
                            'uuid' => 'c75cde8e-681e-4618-b4c9-02f0636bdf25',
                            'public_key' => 'xo0EZEo5eAEEAMVGSnNwqDdaMTZLxY',
                            'private_key' => 'xcFGBGRKOXgBBADFRkpzcKg3WjE2S8WPpXAVNdU',
                            'verify_ssl' => true,
                        ],
                    ],
                ],
            ],
            [
                [
                    'mosparo' => [
                        'instance_url' => 'https://example.com',
                        'uuid' => 'c75cde8e-681e-4618-b4c9-02f0636bdf25',
                        'public_key' => 'xo0EZEo5eAEEAMVGSnNwqDdaMTZLxY',
                        'private_key' => 'xcFGBGRKOXgBBADFRkpzcKg3WjE2S8WPpXAVNdU',
                        'verify_ssl' => false,
                    ],
                ],
                [
                    'enabled' => true,
                    'default_project' => 'default',
                    'projects' => [
                        'default' => [
                            'instance_url' => 'https://example.com',
                            'uuid' => 'c75cde8e-681e-4618-b4c9-02f0636bdf25',
                            'public_key' => 'xo0EZEo5eAEEAMVGSnNwqDdaMTZLxY',
                            'private_key' => 'xcFGBGRKOXgBBADFRkpzcKg3WjE2S8WPpXAVNdU',
                            'verify_ssl' => false,
                        ],
                    ],
                ],
            ],
            [
                [
                    'mosparo' => [
                        'default_project' => 'forms',
                        'projects' => [
                            'forms' => [
                                'instance_url' => 'https://example.com',
                                'uuid' => 'b4c9b4c9-681e-4618-b4c9-02f0636bdf25',
                                'public_key' => 'xo0EZEo5eAEEAMVGSnNwqDdaMTZLxY',
                                'private_key' => 'xcFGBGRKOXgBBADFRkpzcKg3WjE2S8WPpXAVNdU',
                            ],
                            'login' => [
                                'instance_url' => 'https://example.com',
                                'uuid' => 'c75cde8e-681e-4618-b4c9-02f0636bdf25',
                                'public_key' => 'xo0EZEo5eAEEAMVGSnNwqDdaMTZLxY',
                                'private_key' => 'xcFGBGRKOXgBBADFRkpzcKg3WjE2S8WPpXAVNdU',
                            ],
                        ],
                    ],
                ],
                [
                    'enabled' => true,
                    'default_project' => 'forms',
                    'projects' => [
                        'forms' => [
                            'instance_url' => 'https://example.com',
                            'uuid' => 'b4c9b4c9-681e-4618-b4c9-02f0636bdf25',
                            'public_key' => 'xo0EZEo5eAEEAMVGSnNwqDdaMTZLxY',
                            'private_key' => 'xcFGBGRKOXgBBADFRkpzcKg3WjE2S8WPpXAVNdU',
                            'verify_ssl' => true,
                        ],
                        'login' => [
                            'instance_url' => 'https://example.com',
                            'uuid' => 'c75cde8e-681e-4618-b4c9-02f0636bdf25',
                            'public_key' => 'xo0EZEo5eAEEAMVGSnNwqDdaMTZLxY',
                            'private_key' => 'xcFGBGRKOXgBBADFRkpzcKg3WjE2S8WPpXAVNdU',
                            'verify_ssl' => true,
                        ],
                    ],
                ],
            ],
        ];
    }
}
