<?php

namespace statsprovider;

use function is_numeric;
use function mysqli_connect;
use function rtrim;
use function var_dump;

class StatsProvider {

    /**
     * @param array $db
     * @param string $table
     * @param array $stats
     */
    public static function createGameTable(array $db, string $table, array $stats): void {
        $db = mysqli_connect(...$db);
        $query = "CREATE TABLE IF NOT EXISTS " . $table . "(";
        foreach($stats as $key => $value) {
            $type = "TEXT";
            if(is_numeric($value)) {
                $type = "INT";
            }
            $query .= $key . " " . $type . " NOT NULL,";
        }
        $query = rtrim($query, ",");
        $query .= ")";
        $db->query($query);

        $query = "INSERT INTO " . $table . "(";
        foreach($stats as $key => $value) {
            $query .= "`" . $key . "`,";
        }
        $query = rtrim($query, ",");
        $query .= ") VALUES (";
        foreach($stats as $key => $value) {
            $query .= "'" . $value . "',";
        }
        $query = rtrim($query, ",");
        $query .= ")";
        $db->query($query);
        $db->close();
    }

    /**
     * @param array $db
     * @param string $table
     * @param string $key
     * @param int $amount
     * @return array
     */
    public static function getRanking(array $db, string $table, string $key, int $amount): array {
        $db = mysqli_connect(...$db);
        $res = $db->query("SELECT * FROM " . $table . " ORDER BY " . $key . " DESC LIMIT " . $amount);
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