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
	 * @param string $statistic
	 * @param int $limit
	 * @param string $sortOrder
	 *
	 * @return PlayerStatistic[]
	 */
	public static function getTopPlayersByStatistic(string $category, string $statistic, int $limit = 10, string $sortOrder = "DESC"): array {
		$mysql = new MySQLProvider();
		
		$result = $mysql->getTopEntriesByColumn($category, $statistic, $limit, $sortOrder);
		$mysql->close();
		
		return $result;
	}
	
	/**
	 * Creates a new table with the given category name and statistics
	 *
	 *
	 *
	 * @api
	 *
	 * @param string $name
	 * @param string[] $statistics [name => type, ...]
	 * @see MySQLProvider::ALLOWED_TYPES for a list of all passable types
	 * Do not add monthly types and player key here, they will be added automatically created with m_ + name
	 *
	 * @return bool
	 */
	public static function createCategory(string $name, array $statistics): bool {
		$mysql = new MySQLProvider();
		
		$result = $mysql->createCategory($name, $statistics);
		$mysql->close();
		return $result;
	}
	
	/**
	 * Returns the complete statistics for a specific player or null if there is no alltime entry
	 *
	 * @api
	 *
	 * @param string $player
	 * @param string $category
	 *
	 * @return StackedPlayerStatistics|null
	 */
	public static function getStatistics(string $player, string $category): ?StackedPlayerStatistics {
		$mysql = new MySQLProvider();
		
		$result = $mysql->getStatistics($player, $category);
		$mysql->close();
		
		return $result;
	}
	
	/**
	 * Resets the statistics for a specific player (=> removes all entries from the database)
	 *
	 * @api
	 *
	 * @param string $player
	 * @param string $category
	 *
	 * @return bool
	 */
	public static function resetStatistics(string $player, string $category): bool {
		$mysql = new MySQLProvider();
		
		$result = $mysql->removeEntries($player, $category);
		$mysql->close();
		return $result;
	}
	
	/**
	 * Updates a statistic for a player
	 *
	 * @api
	 *
	 * @param string $player
	 * @param string $category
	 * @param string $statistic
	 * @param $value
	 *
	 * @return bool
	 */
	public static function updateStatistic(string $player, string $category, string $statistic, $value): bool {
		$mysql = new MySQLProvider();
		
		$result = $mysql->updateStatistic($player, $category, $statistic, $value);
		$mysql->close();
		
		return $result;
	}
	
	/**
	 * Adds a specific number to a statistic of a player e.g elo (50 appended) will increase 50 steps
	 *
	 * @api
	 *
	 * @param string $player
	 * @param string $category
	 * @param string $statistic
	 * @param int $count
	 *
	 * @return bool
	 */
	public static function appendStatistic(string $player, string $category, string $statistic, int $count): bool {
		$mysql = new MySQLProvider();
		
		$result = $mysql->appendStatistic($player, $category, $statistic, $count);
		$mysql->close();
		
		return $result;
	}
	
	/**
	 * Updates multiple statistics for a player
	 *
	 * @api
	 *
	 * @param string $player
	 * @param array $statistics [category => [key => value, ...], ...]]
	 *
	 * @return bool
	 */
	public static function updateStatistics(string $player, array $statistics): bool {
		$mysql = new MySQLProvider();
		
		$result = $mysql->updateStatistics($player, $statistics);
		$mysql->close();
		
		return $result;
	}
	
	/**
	 * Returns an array with all categories registered
	 *
	 * @api
	 *
	 * @return string[]
	 */
	public static function getCategories(): array {
		$mysql = new MySQLProvider();
		
		$result = $mysql->getCategories();
		$mysql->close();
		return $result;
	}
}