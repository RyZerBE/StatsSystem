<?php

namespace ryzerbe\statssystem\provider;

use baubolp\core\provider\AsyncExecutor;
use Closure;
use pocketmine\Server;
use ryzerbe\statssystem\StatsSystem;

class StatsAsyncProvider {

    /**
     * @param string $name
     * @param array $statistics
     * @param array $defaults
     */
    public static function createCategory(string $name, array $statistics, array $defaults): void{
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(\mysqli $mysqli) use ($name, $statistics, $defaults): void{
            StatsProvider::createCategory($mysqli, $name, $statistics, $defaults);
        });
    }

    /**
     * @param Closure $function - function(array $categories);
     */
    public static function getCategories(Closure $function): void{
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(\mysqli $mysqli): array{
            return StatsProvider::getCategories($mysqli);
        }, function(Server $server, array $categories) use ($function): void{
            $function($categories);
        });
    }

    /**
     * @param string $player
     * @param string $category
     * @param string $key
     * @param mixed $value
     */
    public static function updateStatistic(string $player, string $category, string $key, mixed $value): void{
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(\mysqli $mysqli) use ($player, $category, $key, $value): void{
            StatsProvider::updateStatistic($mysqli, $player, $category, $key, $value);
        });
    }

    /**
     * @param string $player
     * @param string $category
     * @param string $statistic
     * @param int $count
     */
    public static function appendStatistic(string $player, string $category, string $statistic, int $count): void{
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(\mysqli $mysqli) use ($player, $category, $statistic, $count): void{
            StatsProvider::appendStatistic($mysqli, $player, $category, $statistic, $count);
        });
    }

    /**
     * @param string $player
     * @param string $category
     * @param Closure $function - function (?array $statistics);
     */
    public static function getStatistics(string $player, string $category, Closure $function): void{
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(\mysqli $mysqli) use ($player, $category): ?array{
            return StatsProvider::getStatistics($mysqli, $player, $category);
        }, function(Server $server, ?array $stats) use ($function): void{
            $function($stats);
        });
    }

    /**
     * @param string $category
     * @param string $column
     * @param int $limit
     * @param string $sortOrder
     * @param Closure $function - function(array $topEntries);
     */
    public static function getTopEntriesOfColumn(string $category, string $column, Closure $function, int $limit = 10, string $sortOrder = "DESC"): void{
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(\mysqli $mysqli) use ($category, $column, $limit, $sortOrder): array{
            return StatsProvider::getTopEntriesByColumn($mysqli, $category, $column, $limit, $sortOrder);
        }, function(Server $server, array $topEntries) use ($function): void{
            $function($topEntries);
        });
    }

    /**
     * @param string $category
     * @param Closure $function - function(array $columns);
     */
    public static function getColumnsOfCategory(string $category, Closure $function): void{
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(\mysqli $mysqli) use ($category): array{
            return StatsProvider::getColumnsOfCategory($mysqli, $category);
        }, function(Server $server, array $columns) use ($function): void{
            $function($columns);
        });
    }
}