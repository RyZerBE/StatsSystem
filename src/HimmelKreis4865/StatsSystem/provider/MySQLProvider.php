<?php

namespace HimmelKreis4865\StatsSystem\provider;

use HimmelKreis4865\StatsSystem\StatsSystem;
use HimmelKreis4865\StatsSystem\utils\PlayerStatistic;
use HimmelKreis4865\StatsSystem\utils\StackedPlayerStatistics;
use InvalidArgumentException;
use mysqli;
use RuntimeException;
use function array_fill;
use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use function boolval;
use function implode;
use function is_numeric;
use function mysqli_connect;
use function strtoupper;
use function substr;
use const MYSQLI_ASSOC;

class MySQLProvider {
	
	public const ALLOWED_TYPES = [
		"TEXT",
		"INT",
		"INTEGER"
	];
	
	public $mysql;
	
	public function __construct() {
		$this->mysql = mysqli_connect(...StatsSystem::MYSQL_CREDENTIALS);
		
		if (!$this->mysql)
			throw new RuntimeException("Failed to connect to mysql: " . $this->mysql->connect_error);
	}
	
	/**
	 * Returns an array of all categories listed in
	 *
	 * @api
	 *
	 * @return string[]
	 */
	public function getCategories(): array {
		$q = $this->mysql->query("select table_name from information_schema.tables where TABLE_SCHEMA='" . StatsSystem::MYSQL_CREDENTIALS[3] . "';");
		$tables = [];
		while($table = $q->fetch_array(MYSQLI_ASSOC)) {
			if (isset($table["table_name"])) $tables[] = $table["table_name"];
		}
		return $tables;
	}
	
	/**
	 * Returns the statistics for a player
	 *
	 * @api
	 *
	 * @param string $player
	 * @param string $category
	 *
	 * @return StackedPlayerStatistics|null
	 */
	public function getStatistics(string $player, string $category): ?StackedPlayerStatistics {
		$stmt = $this->mysql->prepare("SELECT * FROM " . $category . " WHERE player = ?");
		$stmt->bind_param("s", $player);
		if (!$stmt->execute() or !($result = $stmt->get_result()) or !$result->num_rows) return null;
		
		$statistic = new StackedPlayerStatistics($player);
		foreach ($result->fetch_array(MYSQLI_ASSOC) as $k => $v) {
			if (is_numeric($k) or $k === "player") continue;
			$statistic->{$k} = $v;
		}
		/*$stmt = $this->mysql->prepare("SELECT * FROM " . self::TABLE_MONTHLY . " WHERE player = ?");
		$stmt->bind_param("s", $player);
		if (!$stmt->execute() or !($result = $stmt->get_result()) or !$result->num_rows) return $statistic;
		
		foreach ($result->fetch_array(MYSQLI_ASSOC) as $k => $v) {
			if (is_numeric($k) or $k === "player") continue;
			$statistic->{$k} = $v;
		}*/
		return $statistic;
	}
	
	/**
	 * Returns an array with all top players of a specific category
	 *
	 * @api
	 *
	 * @param string $category
	 * @param string $column
	 * @param int $limit
	 * @param string $sortOrder
	 *
	 * @return PlayerStatistic[]
	 */
	public function getTopEntriesByColumn(string $category, string $column, int $limit = 10, string $sortOrder = "DESC"): array {
		if (!($result = $this->mysql->query("SELECT `player`, `" . $column .  "` FROM " . $category . " ORDER BY " . $column . " " . $sortOrder . " LIMIT 0, " . $limit . ";"))) return [];
		
		$entries = [];
		foreach ($result->fetch_all(MYSQLI_ASSOC) as $data) {
			$entries[] = new PlayerStatistic($data["player"], $data[$column]);
		}
		return $entries;
	}
	
	/**
	 * @api Update a statistic key
	 *
	 * @param string $player
	 * @param string $category
	 * @param string $key
	 * @param scalar $value
	 *
	 * @return bool
	 */
	public function updateStatistic(string $player, string $category, string $key, $value): bool {
		$stmt = $this->mysql->prepare("INSERT INTO " . $category . " (player, " . $key . ") VALUES (?, ?) ON DUPLICATE KEY UPDATE " . $key . " = ?");
		$stmt->bind_param("s" . (is_numeric($value) ? "ii" : "ss"), $player, $value, $value);
		return $stmt->execute();
	}
	
	/**
	 * Update statistics for an array of categories
	 *
	 * @api
	 *
	 * @param string $player
	 * @param array $categories [category => [key => value, ...], ...]]
	 *
	 * @return bool
	 */
	public function updateStatistics(string $player, array $categories): bool {
		$succeed = false;
		foreach ($categories as $category => $statistics) {
			$keys = array_map(function (string $key): string { return "`" . $key . "`"; }, array_keys($statistics));
			$i = 0;
			$updateString = implode(", ", array_map(function ($value) use ($keys, &$i) : string {
				return $keys[$i++] . " = " . $value;
			}, $statistics));
			
			$stmt = $this->mysql->prepare("INSERT INTO " . $category . "(player, " . implode(", ", $keys) . ") VALUES (?, " . implode(", ", array_values($statistics)) . ") ON DUPLICATE KEY UPDATE " . $updateString);
			$stmt->bind_param("s", $player);
			$succeed = $stmt->execute();
		}
		return $succeed;
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
	public function appendStatistic(string $player, string $category, string $statistic, int $count): bool {
		$stmt = $this->mysql->prepare("INSERT INTO " . $category . "(player, `" . $statistic . "`) VALUES (?, " . $count . ") ON DUPLICATE KEY UPDATE `" . $statistic . "` = " . $statistic . " + " . $count);
		$stmt->bind_param("s", $player);
		return $stmt->execute();
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
	public function removeEntries(string $player, string $category): bool {
		// deleting from alltime stats
		$stmt = $this->mysql->prepare("DELETE FROM " . $category . " WHERE player = ?");
		$stmt->bind_param("ss", $category, $player);
		return $stmt->execute();
	}
	
	/**
	 * Creates a new table with the given category name and statistics
	 *
	 * @api
	 *
	 * @param string $name
	 * @param array $statistics
	 *
	 * @return bool
	 */
	public function createCategory(string $name, array $statistics): bool {
		foreach ($statistics as $stat_name => $type) {
			if (!in_array(strtoupper($type), self::ALLOWED_TYPES)) throw new InvalidArgumentException("Type $type is invalid for a database content.");
			$addMonthly = true;
			if ($stat_name[0] === "!") {
				$stat_name = substr($stat_name, 1);
				$addMonthly = false;
			}
			$statistics[$stat_name] = "$stat_name " . strtoupper($type) . " NOT NULL";
			$stat_name = "m_" . $stat_name;
			if ($addMonthly) $statistics[$stat_name] = "$stat_name " . strtoupper($type) . " NOT NULL";
		}
		$statistics = array_merge(["player VARCHAR(16) PRIMARY KEY"], $statistics);
		return boolval($this->mysql->query("CREATE TABLE IF NOT EXISTS `" . StatsSystem::MYSQL_CREDENTIALS[3] . "`." . $name . "(" . implode(", ", $statistics) . ") ENGINE = InnoDB;"));
	}
	
	public function close(): void {
		if ($this->mysql instanceof mysqli) $this->mysql->close();
		$this->mysql = null;
	}
}