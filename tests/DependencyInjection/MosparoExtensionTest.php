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

use Mosparo\MosparoBundle\DependencyInjection\MosparoExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

/**
 * @internal
 */
class MosparoExtensionTest extends TestCase
{
    protected ?ContainerBuilder $configuration;

    protected function tearDown(): void
    {
        $this->configuration = null;
    }

    public function testThrowsExceptionUnlessInstanceUrlSet(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $loader = new MosparoExtension();
        $config = $this->getSampleConfig();
        unset($config['instance_url']);
        $loader->load([$config], new ContainerBuilder());
    }

    public function testThrowsExceptionUnlessUuidSet(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $loader = new MosparoExtension();
        $config = $this->getSampleConfig();
        unset($config['uuid']);
        $loader->load([$config], new ContainerBuilder());
    }

    public function testThrowsExceptionUnlessPublicKeySet(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $loader = new MosparoExtension();
        $config = $this->getSampleConfig();
        unset($config['public_key']);
        $loader->load([$config], new ContainerBuilder());
    }

    public function testThrowsExceptionUnlessPrivateKeySet(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $loader = new MosparoExtension();
        $config = $this->getSampleConfig();
        unset($config['private_key']);
        $loader->load([$config], new ContainerBuilder());
    }

    public function testParameters(): void
    {
        $this->createSampleConfiguration();
        $config = $this->getSampleConfig();
        $this->assertParameter($config['instance_url'], 'mosparo.instance_url');
        $this->assertParameter($config['uuid'], 'mosparo.uuid');
        $this->assertParameter($config['public_key'], 'mosparo.public_key');
        $this->assertParameter($config['private_key'], 'mosparo.private_key');
    }

    public function testExtensionAlias(): void
    {
        $loader = new MosparoExtension();
        self::assertSame('mosparo', $loader->getAlias());
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
instance_url: https://example.com
uuid: c75cde8e-681e-4618-b4c9-02f0636bdf25
public_key: xo0EZEo5eAEEAMVGSnNwqDdaMTZLxY
private_key: xcFGBGRKOXgBBADFRkpzcKg3WjE2S8WPpXAVNdU
EOF;

        return (new Parser())->parse($yaml);
    }

    private function assertParameter(mixed $value, string $key): void
    {
        self::assertSame($value, $this->configuration->getParameter($key), sprintf('%s parameter is correct', $key));
    }
}
