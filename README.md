# StatsSystem
A library plugin for RyZer.BE made by HimmelKreis4865

**NOTICES:** 
 - Inside the code, the statistics are splitted in 2 different types, monthly stats (normal table name) and alltime stats (a_ + normal table name)
 - Replace mysql connect information at `src/HimmelKreis4865/StatsSystem/StatsSystem.php` (Line 25) and read the documentation above

## Features
 - Async SQL queries
 - Simple and fast API
 - Easy handleable commands for adding statistics

## Commands
 - `/statistics` Shows a form where you're able to search your / other statistics
 - `/statistics addhologram` Spawns a new statistic leaderboard as floating text, run this command to get it's full usage
 - `/statistics reset` Reset statistics of a player

## API Usage

### Get a statistics of a player (async)

```php
<?php

use HimmelKreis4865\StatsSystem\utils\AsyncUtils;

AsyncUtils::getStatistics("playername", function(array $result) {
	var_dump($result["elo"], $result["a_elo"]);
});
```

### Reset statistics for a player (async)

```php
<?php

use HimmelKreis4865\StatsSystem\utils\AsyncUtils;

AsyncUtils::resetStatistics("playername");
```

### Update one statistic for a player (async)

```php
<?php

use HimmelKreis4865\StatsSystem\utils\AsyncUtils;

AsyncUtils::updateStatistic("playername", $key, $new_value, $monthly);
```
**NOTICE** `$monthly` is a boolean, if true the statistic will be updated at monthly table, if false the alltime table will be updated, default = false

### Update multiple statistics for a player (async)

```php
<?php

use HimmelKreis4865\StatsSystem\utils\AsyncUtils;

AsyncUtils::updateStatistic("playername", [$key1 => $new_value1, $key2 => $new_value2], $monthly);
```
**NOTICE** `$monthly` is a boolean, if true the statistic will be updated at monthly table, if false the alltime table will be updated, default = false

### Sync usages
For sync queries, replace `AsyncUtils` by `ProviderUtils` in the code snippets shown above.