<?php

namespace ryzerbe\statssystem\hologram;

use baubolp\core\util\LocationUtils;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\utils\MainLogger;
use pocketmine\utils\SingletonTrait;
use ryzerbe\statssystem\hologram\type\PlayerStatsHologram;
use ryzerbe\statssystem\hologram\type\TopEntriesHologram;
use ryzerbe\statssystem\StatsSystem;
use function array_keys;
use function count;
use function var_dump;

class StatsHologramManager {

    use SingletonTrait;
    /** @var StatsHologram[][]  */
    public array $playerHolograms = [];
    /** @var StatsHologram[]  */
    public array $activeStatsHolograms = [];
    /** @var array  */
    public array $holograms = [];


    /**
     * @param StatsHologram $hologram
     */
    public function addActiveStatsHologram(StatsHologram $hologram): void{
        $this->activeStatsHolograms[$hologram->getId()] = $hologram;
    }

    /**
     * @param StatsHologram|string $hologram
     */
    public function removeActiveStatsHologram(StatsHologram|string $hologram): void{
        if($hologram instanceof StatsHologram) $hologram = $hologram->getId();

        unset($this->activeStatsHolograms[$hologram]);
    }

    /**
     * @param StatsHologram $hologram
     * @param Location $location
     * @param string $group
     * @param array $extraData
     */
    public function createHologram(StatsHologram $hologram, Location $location, string $group, array $extraData = []){
        $config = StatsSystem::getStatsConfig();
        $config->set($hologram->getId(), [
            "id" => $hologram->getId(),
            "title" => $hologram->getTitle(),
            "displayTo" => $hologram->getDisplayTo(),
            "type" => $hologram::NETWORK_ID,
            "location" => LocationUtils::toString($location),
            "category" => $hologram->getCategory(),
            "group" => $group,
            "extra" => $extraData
        ]);
        $config->save();
    }

    /**
     * @param int $type
     * @param Location $location
     * @param string $category
     * @param string $title
     * @return StatsHologram|null
     */
    public function getNewHologram(int $type, Location $location, string $category, string $title): ?StatsHologram{
        return match ($type) {
            PlayerStatsHologram::NETWORK_ID => new PlayerStatsHologram($location->asPosition(), $category, $title),
            TopEntriesHologram::NETWORK_ID => new TopEntriesHologram($location->asPosition(), $category, $title),
            default => null,
        };
    }

    public function loadHolograms(): void{
        foreach(StatsSystem::getStatsConfig()->getAll(true) as $hologramId) {
            $data = StatsSystem::getStatsConfig()->get($hologramId);
            $hologram = $this->getNewHologram($data["type"], LocationUtils::fromString($data["location"]), $data["category"], $data["title"]);
            if($hologram instanceof TopEntriesHologram) {
                $hologram->limit = $data["extra"]["limit"];
                $hologram->column = $data["extra"]["sortBy"];
                $hologram->sortOrder = $data["extra"]["sortOrder"];
            }

            $hologram->displayTo((array)$data["displayTo"]);

            $this->holograms[$hologram->getId()] = $hologram;
            var_dump($hologram->getTitle());
        }

        MainLogger::getLogger()->info(count(array_keys($this->holograms))." holograms loaded!");
    }
}