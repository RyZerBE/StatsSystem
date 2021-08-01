<?php

namespace HimmelKreis4865\StatsSystem\utils;

use HimmelKreis4865\StatsSystem\provider\ProviderUtils;
use stdClass;
use function unserialize;
use function serialize;

final class AsyncUtils {
	
	/**
	 * Returns an array with all statistics of a certain player async
	 *
	 * @api
	 *
	 * @param string $player
	 * @param string $category
	 * @param callable $result
	 */
	public static function getStatistics(string $player, string $category, callable $result): void {
		AsyncExecutor::execute(function (stdClass $class) {
			return ProviderUtils::getStatistics($class->player, $class->category);
		}, $result, ["player" => $player, "category" => $category]);
	}
	
	/**
	 * Handles top player request async
	 *
	 * @api
	 *
	 * @param string $category
	 * @param string $statistic
	 * @param callable $result
	 * @param int $limit
	 * @param string $sortOrder
	 */
	public static function getTopPlayersByStatistic(string $category, string $statistic, callable $result, int $limit = 10, string $sortOrder = "DESC"): void {
		AsyncExecutor::execute(function (stdClass $class) {
			return ProviderUtils::getTopPlayersByStatistic($class->category, $class->statistic, $class->limit, $class->sortOrder);
		}, $result, ["category" => $category, "statistic" => $statistic, "limit" => $limit, "sortOrder" => $sortOrder]);
	}
	
	/**
	 * Resets the statistics of a player async
	 *
	 * @api
	 *
	 * @param string $player
	 * @param string $category
	 * @param callable|null $result
	 */
	public static function resetStatistics(string $player, string $category, callable $result = null): void {
		AsyncExecutor::execute(function (stdClass $class) {
			return ProviderUtils::resetStatistics($class->player, $class->category);
		}, $result, ["player" => $player, "category" => $category]);
	}
	
	/**
	 * Resets all statistics of a player
	 *
	 * @api
	 *
	 * @param string $player
	 * @param callable|null $result
	 *
	 * @return void
	 */
	public static function resetAllStatistics(string $player, callable $result = null): void {
		AsyncExecutor::execute(function (stdClass $class) {
			foreach (ProviderUtils::getCategories() as $category) {
				ProviderUtils::resetStatistics($class->player, $category);
			}
			return true;
		}, $result, ["player" => $player]);
	}
	
	/**
	 * Updates a statistic for a player
	 *
	 * @api
	 *
	 * @param string $player
	 * @param string $category
	 * @param string $statistic
	 * @param scalar $value
	 * @param callable|null $result
	 */
	public static function updateStatistic(string $player, string $category, string $statistic, $value, callable $result = null): void {
		AsyncExecutor::execute(function (stdClass $class) {
			return ProviderUtils::updateStatistic($class->player, $class->category, $class->statistic, $class->value);
		}, $result, ["player" => $player, "category" => $category, "statistic" => $statistic, "value" => $value]);
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
	 * @param callable|null $result
	 */
	public static function appendStatistic(string $player, string $category, string $statistic, int $count, callable $result = null): void {
		AsyncExecutor::execute(function (stdClass $class) {
			return ProviderUtils::appendStatistic($class->player, $class->category, $class->statistic, $class->count);
		}, $result, ["player" => $player, "category" => $category, "statistic" => $statistic, "count" => $count]);
	}
	
	/**
	 * Updates multiple statistics for a player
	 *
	 * @api
	 *
	 * @param string $player
	 * @param array $statistics [category => [key => value, ...], ...]]
	 * @param callable|null $result
	 */
	public static function updateStatistics(string $player, array $statistics, callable $result = null): void {
		AsyncExecutor::execute(function (stdClass $class) {
			return ProviderUtils::updateStatistics($class->player, unserialize($class->statistics));
		}, $result, ["player" => $player, "statistics" => serialize($statistics)]);
	}
	
	/**
	 * Returns an array with all statistics of a player
	 *
	 * @api
	 *
	 * @param string $player
	 * @param callable $result function (array[category => [key => value, ...]] $statistics): void
	 *
	 * @return void
	 */
	public static function getAllStatistics(string $player, callable $result): void {
		AsyncExecutor::execute(function (stdClass $class) {
			$statistics = [];
			foreach (ProviderUtils::getCategories() as $category)
			    $statistics[$category] = ProviderUtils::getStatistics($class->player, $category);

			return Utils::filterNullables($statistics);
		}, $result, ["player" => $player]);
	}
}