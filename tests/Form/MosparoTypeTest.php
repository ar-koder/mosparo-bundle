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

namespace Mosparo\MosparoBundle\Tests\Form;

use Mosparo\MosparoBundle\Form\Type\MosparoType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @internal
 */
class MosparoTypeTest extends TypeTestCase
{
    public const INSTANCE_URL = 'http://test.local';
    public const UUID = 'cd98ffa7-0af5-4126-b9aa-c540e0d50e83';
    public const PUBLIC_KEY = 'testPublicKey';
    public const PRIVATE_KEY = 'testPrivateKey';

    protected function getExtensions(): array
    {
        $type = new MosparoType(self::INSTANCE_URL, self::UUID, self::PUBLIC_KEY, self::PRIVATE_KEY);

        return [
            new PreloadedExtension([$type], []),
        ];
    }

    public function testDefaultOptions(): void
    {
        $form = $this->factory->create(MosparoType::class);
        self::assertTrue($form->isSynchronized());

        $view = $form->createView();
        self::assertSame(self::INSTANCE_URL, $view->vars['mosparo']['instance_url']);
        self::assertSame(self::UUID, $view->vars['mosparo']['uuid']);
        self::assertSame(self::PUBLIC_KEY, $view->vars['mosparo']['public_key']);
        self::assertSame(self::PRIVATE_KEY, $view->vars['mosparo']['private_key']);
        self::assertArrayHasKey('options', $view->vars['mosparo']);
        self::assertIsArray($view->vars['mosparo']['options']);
    }
}
