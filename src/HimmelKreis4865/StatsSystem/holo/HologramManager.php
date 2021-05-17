<?php

namespace HimmelKreis4865\StatsSystem\holo;

use HimmelKreis4865\StatsSystem\utils\InstantiableTrait;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\Player;
use function array_keys;
use function var_dump;

final class HologramManager {

	use InstantiableTrait;
	
	/** @var Hologram[][] */
	protected $holograms = [];
	
	/**
	 * @internal
	 *
	 * @param Hologram $hologram
	 */
	public function registerHologram(Hologram $hologram): void {
		if ($hologram->getParticle()->entityId === null) $hologram->particle->encode();
		$this->holograms[$hologram->getLevelName()][$hologram->getParticle()->entityId] = $hologram;
	}
	
	/**
	 * @return Hologram[][]
	 */
	public function getHolograms(): array {
		return $this->holograms;
	}
	
	/**
	 * Sends and removes old holograms
	 *
	 * @internal
	 *
	 * @param Player $player
	 * @param string $oldLevel
	 * @param string $targetLevel
	 */
	public function processLevelUpdate(Player $player, string $oldLevel, string $targetLevel): void {
		foreach (array_keys($this->holograms[$oldLevel] ?? []) as $eid) {
			$pk = new RemoveActorPacket();
			$pk->entityUniqueId = (int) $eid;
			
			$player->sendDataPacket($pk);
		}
		
		foreach ($this->holograms[$targetLevel] ?? [] as $hologram) {
			$hologram->spawnTo($player);
		}
	}
}