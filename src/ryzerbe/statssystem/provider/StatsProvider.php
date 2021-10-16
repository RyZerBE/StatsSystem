<?php

namespace ryzerbe\statssystem\provider;

use mysqli;
use ryzerbe\statssystem\StatsSystem;
use function date;
use function implode;
use function str_starts_with;
use function strtotime;

class StatsProvider {

    public static function getCategories(mysqli $mysqli): array{
        $query = $mysqli->query("SELECT table_name FROM information_schema.tables WHERE TABLE_SCHEMA='" . StatsSystem::DATABASE . "';");
        $tables = [];
        while($tableResult = $query->fetch_all(MYSQLI_ASSOC)){
            foreach($tableResult as $table) $tables[] = $table;
        }
        return $tables;
    }

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

    public static function getStatistics(mysqli $mysqli, string $player, string $category): ?array{
        $query = $mysqli->query("SELECT * FROM " . $category . " WHERE player='$player'");
        if($query->num_rows <= 0) return null;
        return $query->fetch_assoc();
    }

    public static function getTopEntriesByColumn(mysqli $mysqli, string $category, string $column, int $limit = 10, string $sortOrder = "DESC"): array{
        if(!($result = $mysqli->query("SELECT `player`, `" . $column . "` FROM " . $category . " ORDER BY " . $column . " " . $sortOrder . " LIMIT 0, " . $limit . ";"))) return [];
        $entries = [];
        foreach($result->fetch_all(MYSQLI_ASSOC) as $data) $entries[$data["player"]] = $data[$column];
        return $entries;
    }

    /**
     * @param scalar $value
     */
    public static function updateStatistic(mysqli $mysqli, string $player, string $category, string $key, mixed $value): void{
        $mysqli->query("INSERT INTO " . $category . " (player, " . $key . ") VALUES ('$player', '$value') ON DUPLICATE KEY UPDATE " . $key . "='$value'");
    }

    public static function appendStatistic(mysqli $mysqli, string $player, string $category, string $statistic, int $count): void {
        $mysqli->prepare("INSERT INTO " . $category . "(player, `" . $statistic . "`) VALUES ('$player', '$count') ON DUPLICATE KEY UPDATE `" . $statistic . "` = " . $statistic . " + " . $count);
    }

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