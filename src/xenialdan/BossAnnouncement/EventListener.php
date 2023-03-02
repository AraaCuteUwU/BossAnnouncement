<?php

namespace xenialdan\BossAnnouncement;

use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;

class EventListener implements Listener {

    /**
     * @param PlayerJoinEvent $ev
     * @return void
     */
    public function onJoin(PlayerJoinEvent $ev): void {
        if (Loader::getInstance()->isWorldEnabled($ev->getPlayer()->getWorld()->getFolderName())) {
            Loader::getInstance()->bar->addPlayer($ev->getPlayer());
        }
    }

    /**
     * @param PlayerQuitEvent $ev
     * @return void
     */
    public function onLeave(PlayerQuitEvent $ev): void {
        Loader::getInstance()->bar->removePlayer($ev->getPlayer());
    }

    /**
     * @param EntityTeleportEvent $ev
     * @return void
     */
    public function onLevelChange(EntityTeleportEvent $ev): void {
        if ($ev->isCancelled() || !$ev->getEntity() instanceof Player) return;
        Loader::getInstance()->bar->removePlayer($ev->getEntity());
        if (Loader::getInstance()->isWorldEnabled($ev->getTo()->getWorld()->getFolderName())) {
            Loader::getInstance()->bar->addPlayer($ev->getEntity());
        }
    }

}
