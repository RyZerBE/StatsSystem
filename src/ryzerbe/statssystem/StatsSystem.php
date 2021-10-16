<?php

namespace ryzerbe\statssystem;

use pocketmine\plugin\PluginBase;

class StatsSystem extends PluginBase {
    public const DATABASE = "Statistics";

    private static StatsSystem $instance;

    public static function getInstance(): StatsSystem{
        return self::$instance;
    }

    public function onEnable(): void{
        self::$instance = $this;
    }
}