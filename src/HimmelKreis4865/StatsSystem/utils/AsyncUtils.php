<?php

namespace HimmelKreis4865\StatsSystem\utils;

use HimmelKreis4865\StatsSystem\provider\ProviderUtils;
use stdClass;
use Thread;
use function var_dump;

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
			var_dump(Thread::getCurrentThreadId());
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
}