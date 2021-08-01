<?php

namespace HimmelKreis4865\StatsSystem\holo;

use HimmelKreis4865\StatsSystem\utils\PlayerStatistic;
use pocketmine\level\Position;
use pocketmine\utils\TextFormat;
use function array_map;
use function array_merge;
use function implode;

class StatsHologram extends Hologram {
    
    /** @var string $sortOrder */
    protected $sortOrder;
    
    /** @var string $category */
    protected $category;
    
    /** @var string $statistic */
    protected $statistic;
    
    /** @var string|null $title */
    protected $customTitle;
	
	/**
	 * StatsHologram constructor.
	 *
	 * @param Position $position
	 * @param string $category
	 * @param string $statistic
	 * @param string $sortOrder
	 * @param string|null $title
	 */
    public function __construct(Position $position, string $category, string $statistic, string $sortOrder = "DESC", string $title = null) {
        $this->category = $category;
        $this->statistic = $statistic;
        $this->sortOrder = $sortOrder;
        $this->customTitle = $title;
		parent::__construct($position, "", "");
		HologramManager::getInstance()->registerHologram($this);
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
	public function getStatistic(): string {
		return $this->statistic;
	}
	
	/**
	 * @return string
	 */
	public function getCategory(): string {
		return $this->category;
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
		$this->setText(implode("\n", array_merge([($this->customTitle."\n" ?? "§b§l" . $this->getStatistic() . " §c§lLeaderboard\n")], ["\n"], array_map(function (PlayerStatistic $statistic) use (&$k): string {
			return TextFormat::RED . ++$k . TextFormat::DARK_GRAY . ". " . TextFormat::GOLD . $statistic->getPlayer() . TextFormat::DARK_GRAY . ": " . TextFormat::AQUA . $statistic->getStatsCount();
		}, $players))));
	}
	
	/**
	 * @return array
	 */
	public function asArray(): array {
		return [
			"levelName" => $this->getLevelName(),
			"statistic" => $this->getStatistic(),
			"category" => $this->getCategory(),
			"title" => $this->customTitle,
			"sortOrder" => $this->getSortOrder(),
			"position" => ["x" => $this->getParticle()->getFloorX(), "y" => $this->getParticle()->getFloorY(), "z" => $this->getParticle()->getFloorZ()]
		];
	}
}