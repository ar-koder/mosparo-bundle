<?php

/**
 * @package   MosparoBundle
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 * @link      https://github.com/arnaud-ritti/mosparo-bundle
 */

declare(strict_types=1);

namespace Mosparo\MosparoBundle\Services;

use Mosparo\ApiClient\Client;
use Mosparo\ApiClient\Exception;

class MosparoClient extends Client
{
    private static MosparoClient $instance;

    /**
     * @throws Exception
     */
    public static function make(string $host, string $publicKey, string $privateKey, bool $verifySsl = true): self
    {
        if (false === filter_var($host, \FILTER_VALIDATE_URL)) {
            throw new Exception(sprintf('Please check yours "instance_url". "%s" is not a valid URL', $host));
        }

        if (empty(self::$instance)) {
            self::$instance = new self(
                $host,
                $publicKey,
                $privateKey,
                [
                    'verify' => $verifySsl,
                ]
            );
        }

        return self::$instance;
    }
}
