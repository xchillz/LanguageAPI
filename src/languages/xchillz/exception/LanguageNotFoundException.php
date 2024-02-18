<?php

declare(strict_types=1);

namespace languages\xchillz\exception;

use pocketmine\utils\PluginException;

final class LanguageNotFoundException extends PluginException
{

    public function __construct(string $languageId)
    {
        parent::__construct("$languageId language was not previously registered in this API.");
    }

}