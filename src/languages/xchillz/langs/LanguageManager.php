<?php

declare(strict_types=1);

namespace languages\xchillz\langs;

use languages\xchillz\exception\LanguageNotFoundException;
use languages\xchillz\LanguageAPI;

final class LanguageManager
{

    /** @var array<string, Language> */
    private $languages = [];

    public function __construct()
    {
        $langsData = json_decode(file_get_contents(LanguageAPI::getInstance()->getDataFolder() . 'langs.json'), true);

        foreach ($langsData as $langId => $langData) {
            if (!isset($langData['names'])) {
                LanguageAPI::getInstance()->getLogger()->error("An error occurred while loading $langId language. (It could not be loaded)");
                continue;
            }

            $language = new Language($langId, $langData['names']);
            $this->languages[$langId] = $language;
            LanguageAPI::getInstance()->getLogger()->info("$langId language was loaded!");
        }

        LanguageAPI::getInstance()->getLogger()->info(sizeof($this->languages) . " languages were loaded!");
    }

    /**
     * @throws LanguageNotFoundException
     */
    public function loadMessages(array $messagesData)
    {
        foreach ($messagesData as $langId => $messages) {
            if (!isset($this->languages[$langId])) {
                throw new LanguageNotFoundException($langId);
            }

            $this->languages[$langId]->addMessages($messages);
        }
    }

    /**
     * @return Language|null
     */
    public function getLanguageById(string $providedId)
    {
        return $this->languages[$providedId] ?? null;
    }

    /**
     * @return Language|null
     */
    public function getLanguageByName(string $providedName)
    {
        foreach ($this->languages as $language) {
            if ($language->containsName($providedName)) {
                return $language;
            }
        }
        return null;
    }

    /**
     * @return array<string, Language>
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

}
