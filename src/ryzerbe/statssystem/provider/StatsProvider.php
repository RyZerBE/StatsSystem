<?php

namespace ryzerbe\statssystem\provider;

use mysqli;
use ryzerbe\statssystem\StatsSystem;
use function array_keys;
use function array_map;
use function array_values;
use function date;
use function implode;
use function str_starts_with;
use function strtotime;
use const MYSQLI_ASSOC;

class StatsProvider {

    /**
     * @param mysqli $mysqli
     * @return array
     */
    public static function getCategories(mysqli $mysqli): array{
        $query = $mysqli->query("SELECT table_name FROM information_schema.tables WHERE TABLE_SCHEMA='" . StatsSystem::DATABASE . "';");
        $tables = [];
        while($tableResult = $query->fetch_all(MYSQLI_ASSOC)){
            foreach($tableResult as $table) $tables[] = $table["TABLE_NAME"];
        }
        return $tables;
    }

    /**
     * @param mysqli $mysqli
     * @param string $player
     * @param string $category
     */
    public static function checkMonthlyStatistic(mysqli $mysqli, string $player, string $category): void {
        $statistics = self::getStatistics($mysqli, $player, $category);
        if($statistics === null || !isset($statistics["date"])) return;
        $time = strtotime($statistics["date"]);
        if(date("m:o", $time) !== date("m:o")) {
            foreach($statistics as $key => $value) {
                if(!str_starts_with($key, "m_")) continue;
                $mysqli->query("UPDATE " . $category . " SET $key=DEFAULT WHERE player='$player'");
            }
        }
    }

    /**
     * @param mysqli $mysqli
     */
    public static function checkMonthlyStatistics(mysqli $mysqli): void{
        $categories = self::getCategories($mysqli);
        $currentDate = date("m:o");
        foreach($categories as $category) {
            $columns = self::getColumnsOfCategory($mysqli, $category);
            $query = $mysqli->query("SELECT date, player FROM " . $category);
            while($row = $query->fetch_row()) {
                $time = strtotime($row[0]);
                $player = $row[1];
                if(date("m:o", $time) !== $currentDate) {
                    foreach($columns as $column) {
                        $stat = $column["COLUMN_NAME"];
                        if(!str_starts_with($stat, "m_")) continue;
                        $mysqli->query("UPDATE " . $category . " SET " . $stat . "=DEFAULT, date=CURRENT_TIMESTAMP WHERE player='".$player . "'");
                    }
                }
            }
        }
    }

    /**
     * @param mysqli $mysqli
     * @param string $player
     * @param string $category
     * @return array|null
     */
    public static function getStatistics(mysqli $mysqli, string $player, string $category): ?array{
        $query = $mysqli->query("SELECT * FROM " . $category . " WHERE player='$player'");
        if($query->num_rows <= 0) return null;
        return $query->fetch_assoc();
    }

    /**
     * @param mysqli $mysqli
     * @param string $category
     * @param string $column
     * @param int $limit
     * @param string $sortOrder
     * @return array
     */
    public static function getTopEntriesByColumn(mysqli $mysqli, string $category, string $column, int $limit = 10, string $sortOrder = "DESC"): array{
        $result = $mysqli->query("SELECT `player`, `$column` FROM $category ORDER BY $column $sortOrder LIMIT $limit");
        $entries = [];
        foreach($result->fetch_all(MYSQLI_ASSOC) as $data) $entries[$data["player"]] = $data[$column];
        return $entries;
    }

    /**
     * @param mysqli $mysqli
     * @param string $category
     * @return array
     */
    public static function getColumnsOfCategory(mysqli $mysqli, string $category): array{
        $query = $mysqli->query("SELECT Column_name FROM Information_schema.columns WHERE Table_name like '$category'");
        $columns = [];
        while($data = $query->fetch_all(MYSQLI_ASSOC)){
            foreach($data as $column) $columns[] = $column;
        }
        return $columns;
    }

    /**
     * @param scalar $value
     */
    public static function updateStatistic(mysqli $mysqli, string $player, string $category, string $key, mixed $value, bool $monthly = true): void{
        $mysqli->query("INSERT INTO " . $category . " (player, " . $key . ") VALUES ('$player', '$value') ON DUPLICATE KEY UPDATE " . $key . "='$value'");
        if($monthly) $mysqli->query("INSERT INTO " . $category . " (player, m_" . $key . ") VALUES ('$player', '$value') ON DUPLICATE KEY UPDATE m_" . $key . "='$value'");

    }

    /**
     * @param mysqli $mysqli
     * @param string $player
     * @param string $category
     * @param array $statistics
     * @param bool $monthly
     */
    public static function updateStatistics(mysqli $mysqli, string $player, string $category, array $statistics, bool $monthly = true){
        $keys = array_map(function(string $key): string{
            return "`".$key."`";
        }, array_keys($statistics));
        $i = 0;
        $updateString = implode(", ", array_map(function($value) use ($keys, &$i): string{
            return $keys[$i++]." = ".$value;
        }, $statistics));

        if($monthly){
            $i = 0;
            $monthlyKeys = array_map(function(string $key): string{
                return "`m_".$key."`";
            }, array_keys($statistics));
            $monthlyUpdateString = implode(", ", array_map(function($value) use ($monthlyKeys, &$i): string{
                return $monthlyKeys[$i++]." = ".$value;
            }, $statistics));
            $mysqli->query("INSERT INTO ".$category."(player, ".implode(", ", $monthlyKeys).") VALUES ('$player', ".implode(", ", array_values($statistics)).") ON DUPLICATE KEY UPDATE ".$monthlyUpdateString);
        }

        $mysqli->query("INSERT INTO ".$category."(player, ".implode(", ", $keys).") VALUES ('$player', ".implode(", ", array_values($statistics)).") ON DUPLICATE KEY UPDATE ".$updateString);
    }

    public static function appendStatistic(mysqli $mysqli, string $player, string $category, string $statistic, int $count, bool $monthly = true): void{
        if(self::getStatistics($mysqli, $player, $category) === null) $mysqli->query("INSERT INTO ".$category."(player, `".$statistic."`) VALUES ('$player', DEFAULT)");
        $mysqli->query("INSERT INTO ".$category."(player, `".$statistic."`) VALUES ('$player', DEFAULT) ON DUPLICATE KEY UPDATE `".$statistic."` = ".$statistic." + ".$count);
        if($monthly) $mysqli->query("INSERT INTO ".$category."(player, `m_".$statistic."`) VALUES ('$player', '$count') ON DUPLICATE KEY UPDATE `m_".$statistic."` = m_".$statistic." + ".$count);
    }

    public static function deductStatistic(mysqli $mysqli, string $player, string $category, string $statistic, int $count, bool $monthly = true): void{
        if(self::getStatistics($mysqli, $player, $category) === null) $mysqli->query("INSERT INTO ".$category."(player, `".$statistic."`) VALUES ('$player', DEFAULT)");
        $mysqli->query("INSERT INTO ".$category."(player, `".$statistic."`) VALUES ('$player', DEFAULT) ON DUPLICATE KEY UPDATE `".$statistic."` = ".$statistic." - ".$count);
        if($monthly) $mysqli->query("INSERT INTO ".$category."(player, `m_".$statistic."`) VALUES ('$player', '$count') ON DUPLICATE KEY UPDATE `m_".$statistic."` = m_".$statistic." - ".$count);
    }

    /**
     * @param mysqli $mysqli
     * @param string $player
     * @param string $category
     */
    public static function resetStatistics(mysqli $mysqli, string $player, string $category): void{
        $mysqli->query("DELETE FROM `$category` WHERE player='$player'");
    }

    /**
     * @param mysqli $mysqli
     * @param string $name
     * @param array $statistics
     * @param array $defaults
     */
    public static function createCategory(mysqli $mysqli, string $name, array $statistics, array $defaults): void{
        foreach($statistics as $stat_name => $type){
            $addMonthly = true;
            if($stat_name[0] === "!"){
                $stat_name = substr($stat_name, 1);
                $addMonthly = false;
            }
            $statistics[$stat_name] = "$stat_name " . strtoupper($type) . " NOT NULL DEFAULT " . $defaults[$stat_name];
            $monthly_stat_name = "m_" . $stat_name;
            if($addMonthly) $statistics[$monthly_stat_name] = "$monthly_stat_name " . strtoupper($type) . " NOT NULL DEFAULT " . $defaults[$stat_name];
        }
        $statistics = array_merge(["player VARCHAR(16) PRIMARY KEY"], $statistics, ["date TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP"]);
        $mysqli->query("CREATE TABLE IF NOT EXISTS `" . StatsSystem::DATABASE . "`." . $name . "(" . implode(", ", $statistics) . ") ENGINE = InnoDB;");
    }
}