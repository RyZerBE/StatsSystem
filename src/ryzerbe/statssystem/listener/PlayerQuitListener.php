<?php

namespace ryzerbe\statssystem\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Server;
use ryzerbe\statssystem\hologram\StatsHologramManager;

class PlayerQuitListener implements Listener {

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();

        foreach((StatsHologramManager::getInstance()->playerHolograms[$player->getName()] ?? []) as $statsHologram) {
            $hologram = Server::getInstance()->findEntity($statsHologram->entityId);
            $hologram?->flagForDespawn();
            StatsHologramManager::getInstance()->removeActiveStatsHologram($statsHologram);
        }
        unset(StatsHologramManager::getInstance()->playerHolograms[$player->getName()]);
    }
}