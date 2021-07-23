# StatsSystem
A library plugin for RyZer.BE made by HimmelKreis4865

**NOTICES:** 
 - Inside the code, the statistics are splitted in 2 different types, monthly stats (normal table name) and alltime stats (a_ + normal table name)
 - Replace mysql connect information at `src/HimmelKreis4865/StatsSystem/StatsSystem.php` (Line 25) and read the documentation above

## Version 1.1
Introducing 1.1, we move from a monthly and alltime table to categories and their contents.
A category stands for e.g a gamemode you need to collect statistics
Basic example would be bedwars, by accessing the api to create this table with statistics elo (int), wins (int), played (int),
it would create a table with the following structure:

| player (Varchar(16) Primary key) | elo (int) | wins (int) | played (int) | m_elo (int) | m_wins (int) | m_played (int) |
|----------------------------------|-----------|------------|--------------|-------------|--------------|----------------|

monthly entries are created automatically, you can add an entry with no monthly component if you add "!" before the target name when calling the built in api function `ProviderUtils::createCategory()`

## Features
 - Async SQL queries
 - Simple and fast API
 - Easy handleable commands for adding statistics

## Commands
 - `/statistics` Shows a form where you're able to search your / other statistics
 - `/statistics addhologram` Spawns a new statistic leaderboard as floating text, run this command to get it's full usage
 - `/statistics reset` Reset statistics of a player

## API Usage
You can add a callable as parameter at the end for every listed API method except for methods that already have a callable in parameters below

### Get all statistics of one category for a player (async)

```php
<?php

use HimmelKreis4865\StatsSystem\utils\AsyncUtils;

AsyncUtils::getStatistics($playername, $category, function($result) {
	var_dump($result->elo, $result->m_elo);
});
```

### Get all statistics of all categories for a player (async, requires a query for every category)
```php
<?php

use HimmelKreis4865\StatsSystem\utils\AsyncUtils;

AsyncUtils::getAllStatistics($playername, function(array $result) {
	var_dump($result["bedwars"]->elo, $result["bedwars"]->m_elo);
});
```

### Reset statistics of one category for a player (async)

```php
<?php

use HimmelKreis4865\StatsSystem\utils\AsyncUtils;

AsyncUtils::resetStatistics($playername, $category);
```

### Reset statistics of all categories for a player (async)

```php
<?php

use HimmelKreis4865\StatsSystem\utils\AsyncUtils;

AsyncUtils::resetAllStatistics($playername);
```


### Create or Update one statistic for a player (async)

```php
<?php

use HimmelKreis4865\StatsSystem\utils\AsyncUtils;

AsyncUtils::updateStatistic($playername, $category, $key, $value);
```

### Create or Update multiple statistics for a player (async)

```php
<?php

use HimmelKreis4865\StatsSystem\utils\AsyncUtils;

AsyncUtils::updateStatistic($playername, [$category => [$key => $value, $key2 => $value2]]);
```

### Create or Append a statistic (async)
Increases the count of a category statistic by `$count`
To subtract it, use a negative integer
```php
<?php

use HimmelKreis4865\StatsSystem\utils\AsyncUtils;

AsyncUtils::appendStatistic($playername, $category, $statistic, $count);
```

### Create category (sync)
```php
<?php

use HimmelKreis4865\StatsSystem\provider\ProviderUtils;

ProviderUtils::createCategory($name, [$statistic => $type, ...]);
```

`$name` The name of the category you want to create
`$statistic` The name of a statistic you want to add, add a ! at the begin to prevent automatic monthly column generation
 -> The automatic monthly column is named m_ + `$statistic`
`$type` The type of the statistic, this either be `INT` or `TEXT`, no other values are accepted. Do we need more?

### Sync usages
For sync queries, replace `AsyncUtils` by `ProviderUtils` in the code snippets shown above.