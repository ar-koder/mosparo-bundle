<?php

/**
 * @package   MosparoBundle
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 * @link      https://github.com/arnaud-ritti/mosparo-bundle
 */

declare(strict_types=1);

namespace Event;

use Mosparo\MosparoBundle\Event\FilterFieldTypesEvent;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class FilterFieldTypesEventTest extends TestCase
{
    public function testEventGetTypes(): void
    {
        $ignoredFieldTypes = ['captcha', 'password'];
        $verifiableFieldTypes = ['text', 'textarea'];
        $event = new FilterFieldTypesEvent($ignoredFieldTypes, $verifiableFieldTypes);

        self::assertSame($ignoredFieldTypes, $event->getIgnoredFieldTypes());
        self::assertSame($verifiableFieldTypes, $event->getVerifiableFieldTypes());
    }

    public function testEventSetAndGetTypes(): void
    {
        $ignoredFieldTypes = ['captcha', 'password'];
        $verifiableFieldTypes = ['text', 'textarea'];
        $event = new FilterFieldTypesEvent($ignoredFieldTypes, $verifiableFieldTypes);

        $newIgnoredFieldTypes = ['captcha', 'password', 'mosparo'];
        $newVerifiableFieldTypes = ['text', 'textarea', 'email'];

        $event->setIgnoredFieldTypes($newIgnoredFieldTypes);
        $event->setVerifiableFieldTypes($newVerifiableFieldTypes);

        self::assertSame($newIgnoredFieldTypes, $event->getIgnoredFieldTypes());
        self::assertSame($newVerifiableFieldTypes, $event->getVerifiableFieldTypes());
    }
}
