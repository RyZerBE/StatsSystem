<?php

namespace HimmelKreis4865\StatsSystem\tasks;

use HimmelKreis4865\StatsSystem\holo\Hologram;
use HimmelKreis4865\StatsSystem\holo\HologramManager;
use HimmelKreis4865\StatsSystem\holo\StatsHologram;
use HimmelKreis4865\StatsSystem\provider\ProviderUtils;
use HimmelKreis4865\StatsSystem\utils\AsyncUtils;
use pocketmine\level\Level;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use HimmelKreis4865\StatsSystem\utils\AsyncExecutor;
use stdClass;

class StatsRefreshTask extends Task {
	
	public function onRun(int $currentTick) {
		foreach (HologramManager::getInstance()->getHolograms() as $levelName => $holograms) {
			/** @var StatsHologram $hologram */
			foreach (array_filter($holograms, function(Hologram $hologram): bool { return ($hologram instanceof StatsHologram); }) as $hologram) {
				AsyncUtils::getTopPlayersByStatistic($hologram->getCategory(), $hologram->getStatistic(), function (array $players) use ($hologram, $levelName) {
					$hologram->parsePlayers($players);
					if (($level = Server::getInstance()->getLevelByName($levelName)) instanceof Level) {
						foreach ($level->getPlayers() as $player) {
							$hologram->spawnTo($player);
						}
					}
				});
			}
		}
	}
}
/*
 * function (array $players) use ($hologram, $levelName) {
					$hologram->parsePlayers($players);
					if (($level = Server::getInstance()->getLevelByName($levelName)) instanceof Level) {
						foreach ($level->getPlayers() as $player) {
							$hologram->spawnTo($player);
						}
					}
				}*/