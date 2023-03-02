<?php

/*
 * BossAnnouncement
 * A plugin by XenialDan aka thebigsmileXD
 * http://github.com/thebigsmileXD/BossAnnouncement
 * A simple boss bar tile plugin using apibossbar
 */

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use xenialdan\apibossbar\DiverseBossBar;

class BossAnnouncement extends PluginBase implements Listener {
    use SingletonTrait;

    /** @var DiverseBossBar $bar */
    public DiverseBossBar $bar;
    /** @var int $i */
    public int $i = 0;
    /** @var int $changeSpeed */
    public int $changeSpeed = 0;
    /** @var array $subTitles */
    public array $subTitles = [];
    /** @var string $title */
    public string $title = '';


    public function onEnable(): void {
        self::setInstance($this);
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->title = $this->getConfig()->get('head-message', '');
        $this->subTitles = $this->getConfig()->get('changing-messages', []);
        $this->changeSpeed = max(1, $this->getConfig()->get('change-speed', 1));
        $this->bar = (new DiverseBossBar())->setTitle($this->title);
        $this->getScheduler()->scheduleRepeatingTask(new class extends Task {
            public function onRun(): void {
                BossAnnouncement::getInstance()->i++;
                if (BossAnnouncement::getInstance()->i >= count(BossAnnouncement::getInstance()->subTitles)) {
                    BossAnnouncement::getInstance()->i = 0;
                }
                foreach (BossAnnouncement::getInstance()->bar->getPlayers() as $player) {
                    if ($player->isOnline() && BossAnnouncement::getInstance()->isWorldEnabled($player->getWorld()->getFolderName())) {
                        BossAnnouncement::getInstance()->setText($player);
                    }

                }
            }
        }, 20 * $this->changeSpeed);
    }

    /**
     * Generates and sets the output
     *
     * @param Player $player
     */
    public function setText(Player $player): void
    {
        $currentMSG = $this->subTitles[$this->i % count($this->subTitles)];
        if (strpos($currentMSG, '%') > -1) {
            $percentage = substr($currentMSG, 1, strpos($currentMSG, '%') - 1);
            if (is_numeric($percentage)) {
                $this->bar->setPercentageFor([$player], $percentage / 100);
            }
            $currentMSG = substr($currentMSG, strpos($currentMSG, '%') + 2);
        }
        if (!empty($this->title)) {
            $this->bar->setTitleFor([$player], $this->formatText($player, $this->title))->setSubTitleFor([$player], $this->formatText($player, $currentMSG));
        } else {
            $this->bar->setTitleFor([$player], $this->formatText($player, $currentMSG));
        }
    }

    /**
     * Formats the string
     *
     * @param Player $player
     * @param string $text
     * @return string
     */
    public function formatText(Player $player, string $text): string {
        return str_replace(['{display_name}', '{name}', '{x}', '{y}', '{z}', '{world}', '{level_players}', '{server_players}', '{server_max_players}', '{hour}', '{minute}', '{second}', '{BLACK}', '{DARK_BLUE}', '{DARK_GREEN}', '{DARK_AQUA}', '{DARK_RED}', '{DARK_PURPLE}', '{GOLD}', '{GRAY}', '{DARK_GRAY}', '{BLUE}', '{GREEN}', '{AQUA}', '{RED}', '{LIGHT_PURPLE}', '{YELLOW}', '{WHITE}', '{OBFUSCATED}', '{BOLD}', '{STRIKETHROUGH}', '{UNDERLINE}', '{ITALIC}', '{RESET}', '&0', '&1', '&2', '&3', '&4', '&5', '&6', '&7', '&8', '&9', '&a', '&b', '&c', '&d', '&e', '&f', '&k', '&l', '&m', '&n', '&o', '&r'], [$player->getDisplayName(), $player->getName(), $player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ(), ($world = $player->getWorld()) !== null ? $world->getFolderName() : '', count($player->getWorld()->getPlayers()), count($player->getServer()->getOnlinePlayers()), $player->getServer()->getMaxPlayers(), date('H'), date('i'), date('s'), '&0', '&1', '&2', '&3', '&4', '&5', '&6', '&7', '&8', '&9', '&a', '&b', '&c', '&d', '&e', '&f', '&k', '&l', '&m', '&n', '&o', '&r', TextFormat::BLACK, TextFormat::DARK_BLUE, TextFormat::DARK_GREEN, TextFormat::DARK_AQUA, TextFormat::DARK_RED, TextFormat::DARK_PURPLE, TextFormat::GOLD, TextFormat::GRAY, TextFormat::DARK_GRAY, TextFormat::BLUE, TextFormat::GREEN, TextFormat::AQUA, TextFormat::RED, TextFormat::LIGHT_PURPLE, TextFormat::YELLOW, TextFormat::WHITE, TextFormat::OBFUSCATED, TextFormat::BOLD, TextFormat::STRIKETHROUGH, TextFormat::UNDERLINE, TextFormat::ITALIC, TextFormat::RESET], $text);
    }

    /**
     * @param string $levelName
     * @return bool
     */
    public function isWorldEnabled(string $levelName): bool
    {
        $mode = $this->getConfig()->get('mode', 0);
        $configWorlds = array_map(static function (string $worldName): string {
            return strtolower(TextFormat::clean($worldName));
        }, $this->getConfig()->get('worlds', []));
        $levelName = strtolower(TextFormat::clean($levelName));
        return match ($mode) {
            0 => true,
            1 => in_array($levelName, $configWorlds, true),
            2 => !in_array($levelName, $configWorlds, true),
            default => false,
        };
    }

    /**
     * @param PlayerDeathEvent $ev
     * @return void
     */
    public function onDeath(PlayerDeathEvent $ev): void {
        $this->bar->removePlayer($ev->getPlayer())->addPlayer($ev->getPlayer());
    }
}
