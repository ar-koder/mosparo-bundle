<?php

declare(strict_types=1);

namespace Mosparo\MosparoBundle\Tests\DependencyInjection;

use Mosparo\MosparoBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    /**
     * @dataProvider dataForProcessedConfiguration
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
