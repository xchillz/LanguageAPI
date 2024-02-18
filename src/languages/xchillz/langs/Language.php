<?php

declare(strict_types=1);

namespace languages\xchillz\langs;

/**
 * Important note: {@link LanguageManager} creates instances of this class, so you should NOT make it yourself, just a recommendation.
 */
final class Language
{

    /** @var string */
    private $id;
    /** @var array<string, string> */
    private $messages = [];
    /** @var array<string, string> */
    private $names;

    public function __construct(string $id, array $names)
    {
        $this->id = $id;
        $this->names = $names;
    }

    public function addMessages(array $messages)
    {
        foreach ($messages as $messageKey => $message)
        {
            $this->messages[$messageKey] = $message;
        }
    }

    public function containsName(string $providedName): bool
    {
        return in_array($providedName, $this->names);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(Language $language): string
    {
        return $this->names[$language->getId()];
    }

    public function getMessage(string $messageKey, array $replaceables = []): string
    {
        if (!isset($this->messages[$messageKey])) return $messageKey;

        if (empty($replaceables)) return $this->messages[$messageKey];

        return str_ireplace(array_keys($replaceables), array_values($replaceables), $this->messages[$messageKey]);
    }

    public function equals(Language $language): bool
    {
        return $this->getId() === $language->getId();
    }

}