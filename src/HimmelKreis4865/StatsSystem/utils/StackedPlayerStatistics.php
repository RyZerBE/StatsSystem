<?php

namespace HimmelKreis4865\StatsSystem\utils;

use stdClass;

class StackedPlayerStatistics extends stdClass {
	
	/** @var string $owner */
	protected $owner;
	
	/**
	 * PlayerStatistics constructor.
	 *
	 * @param string $owner
	 */
	public function __construct(string $owner) {
		$this->owner = $owner;
	}
	
	/**
	 * @return string
	 */
	public function getOwner(): string {
		return $this->owner;
	}
}