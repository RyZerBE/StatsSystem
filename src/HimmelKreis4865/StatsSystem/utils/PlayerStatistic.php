<?php

namespace HimmelKreis4865\StatsSystem\utils;

class PlayerStatistic {
	/** @var string $player */
	protected $player;
	
	/** @var int $statsCount */
	protected $statsCount;
	
	/**
	 * PlayerStatistic constructor.
	 *
	 * @param string $playerName
	 * @param int $statsCount
	 */
	public function __construct(string $playerName, int $statsCount) {
		$this->player = $playerName;
		$this->statsCount = $statsCount;
	}
	
	/**
	 * @return string
	 */
	public function getPlayer(): string {
		return $this->player;
	}
	
	/**
	 * @return int
	 */
	public function getStatsCount(): int {
		return $this->statsCount;
	}
}