<?php

namespace HimmelKreis4865\StatsSystem;

use HimmelKreis4865\StatsSystem\commands\StatsCommand;
use HimmelKreis4865\StatsSystem\provider\HologramProvider;
use HimmelKreis4865\StatsSystem\tasks\StatsRefreshTask;
use pocketmine\plugin\PluginBase;

class StatsSystem extends PluginBase {
	
	public const PREFIX = "§8[§6StatsSystem§8] §7";
	
	public const ADMINISTRATIVE_PERMISSION = "stats.admin";
	
	public const DATABASE = "Statistics";
	
	/**
	 * Refresh rate of leaderboards (in ticks)
	 */
	public const REFRESH_RATE = 20 * 30;
	
	/** @var null|self $instance */
	protected static $instance = null;
	
	/** @var HologramProvider $hologramProvider */
	protected $hologramProvider;
	
	public function onEnable() {
		self::$instance = $this;
		$this->hologramProvider = new HologramProvider();
		$this->hologramProvider->parseHolograms();
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		$this->getScheduler()->scheduleRepeatingTask(new StatsRefreshTask(), self::REFRESH_RATE);
		$this->getServer()->getCommandMap()->register("StatsSystem", new StatsCommand());
	}
	
	public function onDisable() {
		$this->hologramProvider->storeData();
	}
	
	/**
	 * Returns an instance of itself
	 *
	 * @api
	 *
	 * @return null|self
	 */
	public static function getInstance(): ?self {
		return self::$instance;
	}
}