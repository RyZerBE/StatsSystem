<?php

namespace ryzerbe\statssystem;

use pocketmine\plugin\PluginBase;
use ryzerbe\statssystem\command\StatsCommand;

class StatsSystem extends PluginBase {
    /** @var string  */
    public const DATABASE = "Statistics";
    /** @var StatsSystem  */
    private static StatsSystem $instance;

    /**
     * @return StatsSystem
     */
    public static function getInstance(): StatsSystem{
        return self::$instance;
    }

    public function onEnable(): void{
        self::$instance = $this;
        StatsSystem::getInstance()->getServer()->getCommandMap()->registerAll("statssystem", [
           new StatsCommand()
        ]);
    }
}