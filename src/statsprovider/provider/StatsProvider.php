<?php

namespace statsprovider\provider;

use function array_keys;
use function array_map;
use function array_walk;
use function implode;
use function is_numeric;
use function mysqli_connect;

final class StatsProvider implements Providable {
	
	/**
	 * Creates a dynamic game table
	 *
	 * @api
	 *
	 * @param array $db
	 * @param string $table
	 * @param array $stats
	 *
	 * @return void
	 */
	public static function createGameTable(array $db, string $table, array $stats): void {
		$db = mysqli_connect(...$db);
		
		$data = $stats;
		
		array_walk($data, function (&$value, $key) { $value = $key . " " . (is_numeric($value) ? "INT" : "TEXT") . " NOT NULL"; });
		
		$db->query("CREATE TABLE IF NOT EXISTS " . $table . "(" . implode(", ", $data) . ");");
		
		$db->query("INSERT INTO " . $table . "(" . implode(",", array_map(function (string $key): string {
				return "`" . $key . "`";
			}, array_keys($stats))) . ") VALUES (" . implode(",", array_map(function (string $value): string {
				return "'" . $value . "'";
			}, $stats)) . ");");
		
		$db->close();
	}
	
	/**
	 * Returns the ranking for the top amount of entries by a key
	 *
	 * @api
	 *
	 * @param array $db
	 * @param string $table
	 * @param string $key
	 * @param int $amount
	 *
	 * @return array
	 */
	public static function getRanking(array $db, string $table, string $key, int $amount): array {
		$db = mysqli_connect(...$db);
		$res = $db->query("SELECT * FROM " . $table . " ORDER BY " . $key . " DESC LIMIT " . $amount . ";");
		$placement = 1;
		$result = [];
		if($res->num_rows > 0) {
			while($row = $res->fetch_assoc()) {
				$result[$placement++] = $row;
			}
		}
		$db->close();
		return $result;
	}
}