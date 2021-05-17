<?php

namespace HimmelKreis4865\StatsSystem\commands;

use HimmelKreis4865\StatsSystem\commands\subcommands\AddHologramSubCommand;
use HimmelKreis4865\StatsSystem\commands\subcommands\ResetSubCommand;
use HimmelKreis4865\StatsSystem\commands\subcommands\SubCommand;
use HimmelKreis4865\StatsSystem\forms\StatsBaseForm;
use HimmelKreis4865\StatsSystem\StatsSystem;
use HimmelKreis4865\StatsSystem\utils\SubCommandManagerTrait;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use function array_shift;

class StatsCommand extends Command {
	
	use SubCommandManagerTrait;
	
	public function __construct() {
		parent::__construct("stats", "Displays stats of players", "/stats", ["statistics"]);
		$this->loadSubCommand(new ResetSubCommand());
		$this->loadSubCommand(new AddHologramSubCommand());
	}
	
	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if (isset($args[0]) and $sender->hasPermission(StatsSystem::ADMINISTRATIVE_PERMISSION) and (($subCommand = $this->getSubCommand(array_shift($args))) instanceof SubCommand)) {
			$subCommand->execute($sender, $args);
			return;
		}
		// subcommands might support console
		if (!$sender instanceof Player) return;
		
		$sender->sendForm(new StatsBaseForm($sender));
	}
}