<?php

namespace ryzerbe\statssystem;

use pocketmine\plugin\PluginBase;
use ryzerbe\statssystem\command\StatsCommand;
use ryzerbe\statssystem\hologram\HologramUpdateTask;

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
        $this->getScheduler()->scheduleRepeatingTask(new HologramUpdateTask(), 1);
        $this->getServer()->getCommandMap()->registerAll("statssystem", [
           new StatsCommand()
        ]);
    }
}