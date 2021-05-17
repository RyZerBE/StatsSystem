<?php

namespace HimmelKreis4865\StatsSystem\utils;

use HimmelKreis4865\StatsSystem\commands\subcommands\SubCommand;

trait SubCommandManagerTrait {
	
	/** @var SubCommand[] $subCommands */
	protected $subCommands = [];
	
	/**
	 * Loads a subcommand
	 *
	 * @internal
	 *
	 * @param SubCommand $command
	 */
	public function loadSubCommand(SubCommand $command): void {
		$this->subCommands[$command->getName()] = $command;
	}
	
	/**
	 * Returns a subcommand by name
	 *
	 * @api
	 *
	 * @param string $command
	 *
	 * @return SubCommand|null
	 */
	public function getSubCommand(string $command): ?SubCommand {
		return @$this->subCommands[$command];
	}
}