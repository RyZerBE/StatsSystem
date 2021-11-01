<?php

namespace ryzerbe\statssystem\listener;

use BauboLP\Cloud\Provider\CloudProvider;
use baubolp\core\listener\own\RyZerPlayerAuthEvent;
use baubolp\core\provider\AsyncExecutor;
use pocketmine\event\Listener;
use pocketmine\Server;
use ryzerbe\statssystem\hologram\StatsHologram;
use ryzerbe\statssystem\hologram\StatsHologramManager;
use ryzerbe\statssystem\hologram\type\PlayerStatsHologram;
use function explode;

class PlayerJoinListener implements Listener {

    /**
     * @param RyZerPlayerAuthEvent $event
     */
    public function onJoin(RyZerPlayerAuthEvent $event){
        $player = $event->getRyZerPlayer()->getPlayer();

        $group = explode("-", CloudProvider::getServer())[0] ?? "Lobby";
        StatsHologramManager::getInstance()->playerHolograms[$player->getName()] = [];
        foreach(StatsHologramManager::getInstance()->holograms as $id => $statsHologram){
            if(!$statsHologram instanceof StatsHologram) continue;
            if($statsHologram->getGroup() != $group) continue;
            if($statsHologram instanceof PlayerStatsHologram){
                $statsHologram->playerName = $player->getName();
            }
            $playerName = $player->getName();
            AsyncExecutor::submitClosureTask(20 * 3, function(int $currentTick) use ($statsHologram, $playerName): void{
                $player = Server::getInstance()->getPlayerExact($playerName);
                if($player === null) return;
                $statsHologram->displayPlayer($player);
            });
        }
    }
}