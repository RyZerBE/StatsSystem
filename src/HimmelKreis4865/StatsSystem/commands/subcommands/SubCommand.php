<?php

namespace HimmelKreis4865\StatsSystem\commands\subcommands;

use pocketmine\command\CommandSender;

abstract class SubCommand {
	
	/** @var string $name */
	protected $name;
	
	/**
	 * SubCommand constructor.
	 *
	 * @param string $name
	 */
	public function __construct(string $name) {
		$this->name = $name;
	}
	
	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}
	
	/**
	 * Called on command execution
	 *
	 * @internal
	 *
	 * @param CommandSender $sender
	 * @param array $args
	 *
	 * @return mixed
	 */
	abstract public function execute(CommandSender $sender, array $args);
}