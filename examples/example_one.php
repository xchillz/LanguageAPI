<?php

declare(strict_types=1);

use languages\xchillz\exception\LanguageNotFoundException;
use languages\xchillz\langs\Language;
use languages\xchillz\langs\LanguageManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

final class ExampleOne extends PluginBase
{

    /** @var Language */
    private static $defaultLanguage;
    /** @var array<string, Language> */
    private static $languages = [];

    public function onEnable()
    {
        $languageManager = new LanguageManager();

        $messages = [
            'en_US' => [
                'YOU_ARE_NOW_SPRINTING' => "§aYou are now sprinting.",
                'YOU_ARE_NO_LONGER_SPRINTING' => "§cYou are no longer sprinting.",
                'RUN_IN_GAME' => "§cYou must be in-game to execute this command.",
                'CHANGE_LANGUAGE_USAGE' => "§cUsage: §7<command> <language_id>",
                'LANGUAGE_NOT_REGISTERED' => "§7<lang_id>§c language id is not valid. §7(Available IDs: <available_ids>)",
                'LANGUAGE_CHANGED' => "§aYour language was changed from §7<old_language_name> §ato §7<new_language_name>§a."
            ],
            'es_MX' => [
                'YOU_ARE_NOW_SPRINTING' => "§aAhora estás corriendo.",
                'YOU_ARE_NO_LONGER_SPRINTING' => "§cYa no estás corriendo.",
                'RUN_IN_GAME' => "§cDebes ejecutar este comando dentro del juego.",
                'CHANGE_LANGUAGE_USAGE' => "§cUso: §7<command> <id_del_idioma>",
                'LANGUAGE_NOT_REGISTERED' => "§cEl id: §7<lang_id>§c no es válido. §7(IDs disponibles: <available_ids>)",
                'LANGUAGE_CHANGED' => "§aTu idioma fue cambiado de §7<old_language_name> §aa §7<new_language_name>§a."
            ],
            'pt_BR' => [
                'YOU_ARE_NOW_SPRINTING' => "§aAgora você está correndo.",
                'YOU_ARE_NO_LONGER_SPRINTING' => "§cVocê não está mais correndo.",
                'RUN_IN_GAME' => "§cVocê deve estar no jogo para executar esse comando.",
                'CHANGE_LANGUAGE_USAGE' => "§cUso: §7<command> <id_do_idioma>",
                'LANGUAGE_NOT_REGISTERED' => "§cO ID: §7<lang_id>§c não é válido. §7(IDs disponíveis: <available_ids>)",
                'LANGUAGE_CHANGED' => "§aSua língua foi mudada §7<old_language_name> §apara §7<new_language_name>§a."
            ]
        ];

        try {
            $languageManager->loadMessages($messages);
        } catch (LanguageNotFoundException $exception) {
            $this->getLogger()->logException($exception);
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        self::$languages = $languageManager->getLanguages();
        self::$defaultLanguage = self::$languages['en_US'];

        $this->getServer()->getCommandMap()->register('example_one', new ChangeLanguageCommand(
            'changelanguage',
            '',
            null,
            [ 'setlang' ]
        ));
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener(), $this);
    }

    public static function getDefaultLanguage(): Language
    {
        return self::$defaultLanguage;
    }

    /**
     * @param string $id
     * @return Language|null
     */
    public static function getLanguage(string $id)
    {
        return self::$languages[$id];
    }

    /**
     * @return string[]
     */
    public static function getLanguageIds(): array
    {
        return array_keys(self::$languages);
    }

}

final class CustomPlayer extends Player
{

    /** @var Language */
    private $language;

    public function sendTranslatedMessage(string $messageKey, array $replaceables = [])
    {
        $this->sendMessage($this->getLanguage()->getMessage($messageKey, $replaceables));
    }

    public function setLanguage(Language $language)
    {
        $this->language = $language;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

}

final class PlayerListener implements Listener
{

    public function onPlayerCreation(PlayerCreationEvent $event)
    {
        $event->setPlayerClass(CustomPlayer::class);
    }

    public function onPlayerJoin(PlayerJoinEvent $event)
    {
        /** @var CustomPlayer $player */
        $player = $event->getPlayer();

        $player->setLanguage(ExampleOne::getDefaultLanguage());
    }

    /**
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onPlayerToggleSprint(PlayerToggleSprintEvent $event)
    {
        /** @var CustomPlayer $player */
        $player = $event->getPlayer();

        if ($event->isSprinting()) {
            $player->sendTranslatedMessage('YOU_ARE_NOW_SPRINTING');
            return;
        }

        $player->sendTranslatedMessage('YOU_ARE_NO_LONGER_SPRINTING');
    }

}

final class ChangeLanguageCommand extends Command
{

    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if (!($sender instanceof CustomPlayer)) {
            $sender->sendMessage(ExampleOne::getDefaultLanguage()->getMessage('RUN_IN_GAME'));
            return;
        }

        if (!isset($args[0])) {
            $sender->sendTranslatedMessage('CHANGE_LANGUAGE_USAGE', [
                '<command>' => $commandLabel
            ]);
            return;
        }

        $language = ExampleOne::getLanguage($args[0]);

        if ($language === null) {
            $sender->sendTranslatedMessage('LANGUAGE_NOT_REGISTERED', [
                '<lang_id>' => $args[0],
                '<available_ids>' => implode(', ', ExampleOne::getLanguageIds())
            ]);
            return;
        }

        $sender->sendTranslatedMessage('LANGUAGE_CHANGED', [
            '<old_language_name>' => $sender->getLanguage()->getName($language),
            '<new_language_name>' => $language->getName($language)
        ]);
        $sender->setLanguage($language);
    }

}
