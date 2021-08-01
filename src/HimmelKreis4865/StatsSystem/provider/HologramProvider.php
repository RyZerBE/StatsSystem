<?php

namespace HimmelKreis4865\StatsSystem\provider;

use HimmelKreis4865\StatsSystem\holo\HologramManager;
use HimmelKreis4865\StatsSystem\holo\StatsHologram;
use HimmelKreis4865\StatsSystem\StatsSystem;
use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\utils\Config;

class HologramProvider {
	
	/** @var Config $file */
	protected $file;
	
	public function __construct() {
		$this->file = new Config("/root/RyzerCloud/data/stats_holograms.yml");
	}
	
	/**
	 * Called on startup to load existing holograms
	 *
	 * @internal
	 */
	public function parseHolograms(): void {
		foreach ($this->file->getAll() as $levelName => $holograms) {
			if (!count($holograms)) continue;
			
			if (($level = Server::getInstance()->getLevelByName($levelName)) === null) {
				Server::getInstance()->loadLevel($levelName);
				if (($level = Server::getInstance()->getLevelByName($levelName)) === null) continue;
			}
			
			foreach ($holograms as $hologram) {
				new StatsHologram(new Position($hologram["position"]["x"], $hologram["position"]["y"], $hologram["position"]["z"], $level), $hologram["category"], $hologram["statistic"], $hologram["sortOrder"], @$hologram["title"]);
			}
		}
	}
	
	/**
	 * Returns the RAW holograms for a level (as array)
	 *
	 * @api
	 *
	 * @param string $levelName
	 *
	 * @return array
	 */
	public function getHolograms(string $levelName): array {
		return $this->file->get($levelName, null) ?? [];
	}
	
	public function storeData(): void {
		$data = [];
		foreach (HologramManager::getInstance()->getHolograms() as $level => $holograms) {
			foreach ($holograms as $hologram) {
				$data[$level][] = $hologram->asArray();
			}
		}
		$this->file->setAll($data);
		$this->file->save();
	}
}