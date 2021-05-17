<?php

namespace HimmelKreis4865\StatsSystem\provider;

use HimmelKreis4865\StatsSystem\StatsSystem;
use HimmelKreis4865\StatsSystem\utils\PlayerStatistic;
use HimmelKreis4865\StatsSystem\utils\StackedPlayerStatistics;
use mysqli;
use RuntimeException;
use function is_numeric;
use function json_encode;
use function mysqli_connect;
use function var_dump;
use const MYSQLI_ASSOC;

class MySQLProvider {
	
	protected $mysql;
	
	protected const TABLE_ALLTIME = "alltime";
	
	protected const TABLE_MONTHLY = "monthly";
	
	public function __construct() {
		$this->mysql = mysqli_connect(...StatsSystem::MYSQL_CREDENTIALS);
		
		if (!$this->mysql)
			throw new RuntimeException("Failed to connect to mysql: " . $this->mysql->connect_error);
	}
	
	/**
	 * Returns the statistics for a player
	 *
	 * @api
	 *
	 * @param string $player
	 *
	 * @return StackedPlayerStatistics|null
	 */
	public function getStatistics(string $player): ?StackedPlayerStatistics {
		$stmt = $this->mysql->prepare("SELECT * FROM " . self::TABLE_ALLTIME . " WHERE player = ?");
		$stmt->bind_param("s", $player);
		if (!$stmt->execute() or !($result = $stmt->get_result()) or !$result->num_rows) return null;
		
		$statistic = new StackedPlayerStatistics($player);
		foreach ($result->fetch_array(MYSQLI_ASSOC) as $k => $v) {
			if (is_numeric($k) or $k === "player") continue;
			$statistic->{StatsSystem::ALLTIME_PREFIX . $k} = $v;
		}
		$stmt = $this->mysql->prepare("SELECT * FROM " . self::TABLE_MONTHLY . " WHERE player = ?");
		$stmt->bind_param("s", $player);
		if (!$stmt->execute() or !($result = $stmt->get_result()) or !$result->num_rows) return $statistic;
		
		foreach ($result->fetch_array(MYSQLI_ASSOC) as $k => $v) {
			if (is_numeric($k) or $k === "player") continue;
			$statistic->{$k} = $v;
		}
		return $statistic;
	}
	
	/**
	 * Returns an array with all top players of a specific category
	 *
	 * @api
	 *
	 * @param string $column
	 * @param bool $monthly
	 * @param int $limit
	 * @param string $sortOrder
	 *
	 * @return PlayerStatistic[]
	 */
	public function getTopEntriesByColumn(string $column, bool $monthly = false, int $limit = 10, string $sortOrder = "DESC"): array {
		if (!($result = $this->mysql->query("SELECT `player`, `" . $column .  "` FROM " . ($monthly ? self::TABLE_MONTHLY : self::TABLE_ALLTIME) . " ORDER BY " . $column . " " . $sortOrder . " LIMIT 0, " . $limit . ";"))) return [];
		
		$entries = [];
		foreach ($result->fetch_all(MYSQLI_ASSOC) as $data) {
			var_dump("mysql data : " . json_encode($data));
			$entries[] = new PlayerStatistic($data["player"], $data[$column]);
		}
		return $entries;
	}
	
	/**
	 * @api Update a statistic key monthly / alltime
	 *
	 * @param string $player
	 * @param string $key
	 * @param $value
	 * @param bool $monthly
	 *
	 * @return bool
	 */
	public function updateStatistic(string $player, string $key, $value,  bool $monthly = false): bool {
		$stmt = $this->mysql->prepare("UPDATE " . ($monthly ? self::TABLE_MONTHLY : self::TABLE_ALLTIME) . " SET " . $key . " = ? WHERE player = ?");
		$stmt->bind_param((is_numeric($value) ? "i" : "s") . "s", $value, $player);
		return $stmt->execute();
	}
	
	/**
	 * Resets the statistics for a specific player (=> removes all entries from the database)
	 *
	 * @api
	 *
	 * @param string $player
	 */
	public function removeEntries(string $player): void {
		// deleting from alltime stats
		$stmt = $this->mysql->prepare("DELETE FROM " . self::TABLE_ALLTIME . " WHERE player = ?");
		$stmt->bind_param("s", $player);
		$stmt->execute();
		
		// deleting from monthly stats
		$stmt = $this->mysql->prepare("DELETE FROM " . self::TABLE_MONTHLY . " WHERE player = ?");
		$stmt->bind_param("s", $player);
		$stmt->execute();
	}
	
	public function close(): void {
		if ($this->mysql instanceof mysqli) $this->mysql->close();
		$this->mysql = null;
	}
}