<?php

namespace HimmelKreis4865\StatsSystem;

use HimmelKreis4865\StatsSystem\holo\HologramManager;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;

class EventListener implements Listener {
	
	public function onLevelChange(EntityLevelChangeEvent $event) {
		/** @var Player $player blame phpstorm */
		if (($player = $event->getEntity()) instanceof Player) {
			HologramManager::getInstance()->processLevelUpdate($player, $event->getOrigin()->getFolderName(), $event->getTarget()->getFolderName());
		}
	}
	
	public function onJoin(PlayerJoinEvent $event) {
		HologramManager::getInstance()->processLevelUpdate($event->getPlayer(), "-------", $event->getPlayer()->getLevelNonNull()->getFolderName());
	}
}