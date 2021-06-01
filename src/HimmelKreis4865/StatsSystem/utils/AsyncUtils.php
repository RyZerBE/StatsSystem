<?php

namespace HimmelKreis4865\StatsSystem\utils;

use HimmelKreis4865\StatsSystem\provider\ProviderUtils;
use stdClass;
use function serialize;
use function unserialize;

class AsyncUtils {
	/**
	 * Returns an array with all statistics of a certain player async
	 *
	 * @api
	 *
	 * @param string $player
	 * @param callable $result
	 */
	public static function getStatistics(string $player, callable $result): void {
		AsyncExecutor::execute(function (stdClass $class) {
			return ProviderUtils::getStatistics($class->player);
		}, $result, ["player" => $player]);
	}
	
	/**
	 * Handles top player request async
	 *
	 * @api
	 *
	 * @param string $category
	 * @param callable $result
	 * @param bool $monthly
	 * @param int $limit
	 * @param string $sortOrder
	 */
	public static function getTopPlayersByCategory(string $category, callable $result, bool $monthly = false, int $limit = 10, string $sortOrder = "DESC"): void {
		AsyncExecutor::execute(function (stdClass $class) {
			return ProviderUtils::getTopPlayersByCategory($class->player, $class->monthly, $class->limit, $class->sortOrder);
		}, $result, ["category" => $category, "monthly" => $monthly, "limit" => $limit, "sortOrder" => $sortOrder]);
	}
	
	/**
	 * Resets the statistics of a player async
	 *
	 * @api
	 *
	 * @param string $player
	 */
	public static function resetStatistics(string $player): void {
		AsyncExecutor::execute(function (stdClass $class) {
			ProviderUtils::resetStatistics($class->player);
		}, null, ["player" => $player]);
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
		AsyncExecutor::execute(function (stdClass $class) {
			ProviderUtils::updateStatistic($class->player, $class->statistic, $class->value, $class->monthly);
		}, null, ["player" => $player, "statistic" => $statistic, "value" => $value, "monthly" => $monthly]);
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
		AsyncExecutor::execute(function (stdClass $class) {
			ProviderUtils::updateStatistics($class->player, unserialize($class->statistics), $class->monthly);
		}, null, ["player" => $player, "statistics" => serialize($statistics), "monthly" => $monthly]);
	}
}