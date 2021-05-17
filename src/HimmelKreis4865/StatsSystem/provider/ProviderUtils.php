<?php

namespace HimmelKreis4865\StatsSystem\provider;

use HimmelKreis4865\StatsSystem\utils\PlayerStatistic;
use HimmelKreis4865\StatsSystem\utils\StackedPlayerStatistics;

final class ProviderUtils {
	
	/**
	 * Returns an array with all top players of a specific category
	 *
	 * @api
	 *
	 * @param string $category
	 * @param bool $monthly
	 * @param int $limit
	 * @param string $sortOrder
	 *
	 * @return PlayerStatistic[]
	 */
	public static function getTopPlayersByCategory(string $category, bool $monthly = false, int $limit = 10, string $sortOrder = "DESC"): array {
		$mysql = new MySQLProvider();
		
		$result = $mysql->getTopEntriesByColumn($category, $monthly, $limit, $sortOrder);
		$mysql->close();
		
		return $result;
	}
	
	/**
	 * Returns the complete statistics for a specific player or null if there is no alltime entry
	 *
	 * @api
	 *
	 * @param string $player
	 *
	 * @return StackedPlayerStatistics|null
	 */
	public static function getStatistics(string $player): ?StackedPlayerStatistics {
		$mysql = new MySQLProvider();
		
		$result = $mysql->getStatistics($player);
		$mysql->close();
		
		return $result;
	}
	
	/**
	 * Resets the statistics for a specific player (=> removes all entries from the database)
	 *
	 * @api
	 *
	 * @param string $player
	 */
	public static function resetStatistics(string $player): void {
		$mysql = new MySQLProvider();
		
		$mysql->removeEntries($player);
		$mysql->close();
	}
}