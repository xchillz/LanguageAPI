<?php

declare(strict_types=1);

namespace languages\xchillz;

use languages\xchillz\langs\LanguageManager;
use pocketmine\plugin\PluginBase;

final class LanguageAPI extends PluginBase
{

    /** @var LanguageAPI */
    private static $instance;
    /** @var LanguageManager */
    private $languageManager;

    public function onEnable()
    {
        self::$instance = $this;

        $this->saveResource('langs.json');

        $this->languageManager = new LanguageManager();

        $this->getLogger()->info('LanguageAPI successfully enabled.');
    }

    public static function getInstance(): LanguageAPI
    {
        return self::$instance;
    }

    public function getLanguageManager(): LanguageManager
    {
        return $this->languageManager;
    }

}