<?php

namespace HimmelKreis4865\StatsSystem\tasks;

use HimmelKreis4865\StatsSystem\holo\Hologram;
use HimmelKreis4865\StatsSystem\holo\HologramManager;
use HimmelKreis4865\StatsSystem\holo\StatsHologram;
use HimmelKreis4865\StatsSystem\provider\ProviderUtils;
use pocketmine\level\Level;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use HimmelKreis4865\StatsSystem\utils\AsyncExecutor;
use stdClass;

class StatsRefreshTask extends Task {
	
	public function onRun(int $currentTick) {
		foreach (HologramManager::getInstance()->getHolograms() as $levelName => $holograms) {
			foreach (array_filter($holograms, function(Hologram $hologram): bool { return ($hologram instanceof StatsHologram); }) as $hologram) {
				AsyncExecutor::execute(function(stdClass $class): array {
					return ProviderUtils::getTopPlayersByCategory($class->type, $class->monthly, 10, $class->sortOrder);
				}, function (array $players) use ($hologram, $levelName) {
					$hologram->parsePlayers($players);
					if (($level = Server::getInstance()->getLevelByName($levelName)) instanceof Level) {
						foreach ($level->getPlayers() as $player) {
							$hologram->spawnTo($player);
						}
					}
				}, ["type" => $hologram->getType(), "monthly" => $hologram->isMonthly(), "sortOrder" => $hologram->getSortOrder()]);
			}
		}
	}
}