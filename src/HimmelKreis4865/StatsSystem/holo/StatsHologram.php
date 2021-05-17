<?php

namespace HimmelKreis4865\StatsSystem\holo;

use HimmelKreis4865\StatsSystem\utils\PlayerStatistic;
use pocketmine\level\Position;
use pocketmine\utils\TextFormat;
use function array_map;
use function array_merge;
use function implode;

class StatsHologram extends Hologram {
 
	/** @var string $type */
    protected $type;
    
    /** @var bool $monthly */
    protected $monthly;
    
    /** @var string $sortOrder */
    protected $sortOrder;
	
	/**
	 * StatsHologram constructor.
	 *
	 * @param Position $position
	 * @param string $type
	 * @param bool $monthly
	 * @param string $sortOrder
	 */
    public function __construct(Position $position, string $type, bool $monthly = false, string $sortOrder = "DESC") {
        $this->type = $type;
        $this->sortOrder = $sortOrder;
        $this->monthly = $monthly;
		parent::__construct($position, "", "");
		HologramManager::getInstance()->registerHologram($this);
	}
	
	/**
	 * @return bool
	 */
	public function isMonthly(): bool {
		return $this->monthly;
	}
	
	/**
	 * @return string
	 */
	public function getSortOrder(): string {
		return $this->sortOrder;
	}
	
	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}
	
	/**
	 * Parses the hologram for the top players
	 *
	 * @internal
	 *
	 * @param array $players
	 */
	public function parsePlayers(array $players): void {
    	$k = 0;
		$this->setText(implode("\n", array_merge(["§c§l" . $this->getType() . " Leaderboard"], ["\n"], array_map(function (PlayerStatistic $statistic) use (&$k): string {
			return TextFormat::GOLD . ++$k . TextFormat::DARK_GRAY . ". " . TextFormat::GOLD . $statistic->getPlayer() . TextFormat::DARK_GRAY . ": " . TextFormat::GOLD . $statistic->getStatsCount();
		}, $players))));
	}
	
	/**
	 * @return array
	 */
	public function asArray(): array {
		return [
			"levelName" => $this->getLevelName(),
			"type" => $this->getType(),
			"monthly" => $this->isMonthly(),
			"sortOrder" => $this->getSortOrder(),
			"position" => ["x" => $this->getParticle()->getFloorX(), "y" => $this->getParticle()->getFloorY(), "z" => $this->getParticle()->getFloorZ()]
		];
	}
}