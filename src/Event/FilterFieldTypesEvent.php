<?php

/**
 * @package   MosparoBundle
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 * @link      https://github.com/arnaud-ritti/mosparo-bundle
 */

declare(strict_types=1);

namespace Mosparo\MosparoBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class FilterFieldTypesEvent extends Event
{
    protected array $ignoredFieldTypes;

    protected array $verifiableFieldTypes;

    public function __construct(array $ignoredFieldTypes = [], array $verifiableFieldTypes = [])
    {
        $this->ignoredFieldTypes = $ignoredFieldTypes;
        $this->verifiableFieldTypes = $verifiableFieldTypes;
    }

    public function getIgnoredFieldTypes(): array
    {
        return $this->ignoredFieldTypes;
    }

    public function setIgnoredFieldTypes($ignoredFieldTypes): void
    {
        $this->ignoredFieldTypes = $ignoredFieldTypes;
    }

    public function getVerifiableFieldTypes(): array
    {
        return $this->verifiableFieldTypes;
    }

    public function setVerifiableFieldTypes($verifiableFieldTypes): void
    {
        $this->verifiableFieldTypes = $verifiableFieldTypes;
    }
}
