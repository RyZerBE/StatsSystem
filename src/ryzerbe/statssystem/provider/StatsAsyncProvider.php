<?php

namespace ryzerbe\statssystem\provider;

use baubolp\core\provider\AsyncExecutor;
use Closure;
use mysqli;
use pocketmine\Server;
use ryzerbe\statssystem\StatsSystem;

class StatsAsyncProvider {

    /**
     * @param string $name
     * @param array $statistics
     * @param array $defaults
     */
    public static function createCategory(string $name, array $statistics, array $defaults): void{
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli) use ($name, $statistics, $defaults): void{
            StatsProvider::createCategory($mysqli, $name, $statistics, $defaults);
        });
    }

    /**
     * @param Closure $function - function(array $categories);
     */
    public static function getCategories(Closure $function): void{
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli): array{
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
     * @param bool $monthly
     */
    public static function updateStatistic(string $player, string $category, string $key, mixed $value, bool $monthly = true): void{
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli) use ($player, $category, $key, $value, $monthly): void{
            StatsProvider::checkMonthlyStatistic($mysqli, $player, $category);
            StatsProvider::updateStatistic($mysqli, $player, $category, $key, $value, $monthly);
        });
    }

    /**
     * @param string $player
     * @param string $category
     * @param array $statistics
     * @param bool $monthly
     */
    public static function updateStatistics(string $player, string $category, array $statistics, bool $monthly = true): void{
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli) use ($player, $category, $statistics, $monthly): void{
            StatsProvider::checkMonthlyStatistic($mysqli, $player, $category);
            StatsProvider::updateStatistics($mysqli, $player, $category, $statistics, $monthly);
        });
    }

    /**
     * @param string $player
     * @param string $category
     * @param string $statistic
     * @param int $count
     * @param bool $monthly
     */
    public static function appendStatistic(string $player, string $category, string $statistic, int $count, bool $monthly = true): void{
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli) use ($player, $category, $statistic, $count, $monthly): void{
            StatsProvider::checkMonthlyStatistic($mysqli, $player, $category);
            StatsProvider::appendStatistic($mysqli, $player, $category, $statistic, $count, $monthly);
        });
    }

    /**
     * @param string $player
     * @param string $category
     * @param Closure|null $completeFunction
     */
    public static function resetStatistics(string $player, string $category, ?Closure $completeFunction = null): void{
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli) use ($player, $category): void{
            StatsProvider::resetStatistics($mysqli, $player, $category);
        }, $completeFunction);
    }

    /**
     * @param string $player
     * @param string $category
     * @param Closure $function - function (?array $statistics);
     */
    public static function getStatistics(string $player, string $category, Closure $function): void{
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli) use ($player, $category): ?array{
            StatsProvider::checkMonthlyStatistic($mysqli, $player, $category);
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
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli) use ($category, $column, $limit, $sortOrder): array{
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
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli) use ($category): array{
            return StatsProvider::getColumnsOfCategory($mysqli, $category);
        }, function(Server $server, array $columns) use ($function): void{
            $function($columns);
        });
    }

    public static function checkMonthlyStatistics(): void{
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli): void{
            StatsProvider::checkMonthlyStatistics($mysqli);
        });
    }
}