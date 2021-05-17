<?php

namespace HimmelKreis4865\StatsSystem\commands\subcommands;

use HimmelKreis4865\StatsSystem\StatsSystem;
use HimmelKreis4865\StatsSystem\utils\AsyncUtils;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\MainLogger;

class ResetSubCommand extends SubCommand {
	
	public function __construct() {
		parent::__construct("reset");
	}
	
	public function execute(CommandSender $sender, array $args) {
		$target = $args[0] ?? $sender->getName();
		
		if ($sender instanceof ConsoleCommandSender and !isset($args[0])) {
			MainLogger::getLogger()->warning("Please run this command ingame or specify a target player!");
			return;
		}
		
		$sender->sendMessage(StatsSystem::PREFIX . "You successfully reset " . (($target === $sender->getName()) ? "your statistics." : "the statistics of §6" . $target));
		AsyncUtils::resetStatistics($target);
	}
}