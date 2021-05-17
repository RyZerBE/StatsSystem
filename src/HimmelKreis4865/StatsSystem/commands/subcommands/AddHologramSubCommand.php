<?php

namespace HimmelKreis4865\StatsSystem\commands\subcommands;

use HimmelKreis4865\StatsSystem\holo\StatsHologram;
use HimmelKreis4865\StatsSystem\StatsSystem;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use function in_array;
use function strtoupper;

class AddHologramSubCommand extends SubCommand {
	
	public function __construct() {
		parent::__construct("addhologram");
	}
	
	public function execute(CommandSender $sender, array $args) {
		if (!$sender instanceof Player) return;
		if (!isset($args[0])) {
			$sender->sendMessage(StatsSystem::PREFIX . "Usage: §f/stats addhologram <type: string> [monthly: bool] [sortOrder: string (ASC|DESC)]");
			return;
		}
		
		$hologram = new StatsHologram($sender, $args[0], ($args[1] ?? "false" === "true"), (in_array(strtoupper($args[2] ?? ""), ["ASC", "DESC"]) ? strtoupper($args[2]) : "DESC"));
		foreach ($sender->getLevelNonNull()->getPlayers() as $player) {
			$hologram->spawnTo($player);
		}
	}
}