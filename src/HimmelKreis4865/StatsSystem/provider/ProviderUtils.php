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
	
	/**
	 * Updates a statistic for a player
	 *
	 * @api
	 *
	 * @param string $player
	 * @param string $statistic
	 * @param $value
	 * @param bool $monthly
	 */
	public static function updateStatistic(string $player, string $statistic, $value, bool $monthly = false): void {
		$mysql = new MySQLProvider();
		
		$mysql->updateStatistic($player, $statistic, $value, $monthly);
		$mysql->close();
	}
	
	/**
	 * Updates multiple statistics for a player
	 *
	 * @api
	 *
	 * @param string $player
	 * @param array $statistics [database_key => new_value]
	 * @param bool $monthly
	 */
	public static function updateStatistics(string $player, array $statistics, bool $monthly = false): void {
		$mysql = new MySQLProvider();
		
		$mysql->updateStatistics($player, $statistics, $monthly);
		$mysql->close();
	}
}