<?php

namespace ryzerbe\statssystem;

use BauboLP\Cloud\Provider\CloudProvider;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use ryzerbe\statssystem\command\StatsCommand;
use ryzerbe\statssystem\hologram\HologramUpdateTask;
use ryzerbe\statssystem\hologram\StatsHologramManager;
use ryzerbe\statssystem\listener\PlayerJoinListener;
use ryzerbe\statssystem\listener\PlayerQuitListener;
use ryzerbe\statssystem\provider\StatsAsyncProvider;
use function explode;
use function file_exists;

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
        StatsHologramManager::getInstance();

        $this->initConfig();
        $this->getScheduler()->scheduleRepeatingTask(new HologramUpdateTask(), 1);

        $this->getServer()->getCommandMap()->registerAll("statssystem", [
           new StatsCommand()
        ]);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerJoinListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerQuitListener(), $this);

        $group = explode("-", CloudProvider::getServer())[0] ?? null;
        if($group === "Lobby") {
            StatsAsyncProvider::checkMonthlyStatistics();
        }
    }

    public function initConfig(): void{
        if(!file_exists("/root/RyzerCloud/data/statsholograms.json")){
            $config = new Config("/root/RyzerCloud/data/statsholograms.json", Config::JSON);
            $config->save();
        }
        StatsHologramManager::getInstance()->loadHolograms();
    }

    /**
     * @return Config
     */
    public static function getStatsConfig(): Config{
        return new Config("/root/RyzerCloud/data/statsholograms.json", Config::JSON);
    }
}