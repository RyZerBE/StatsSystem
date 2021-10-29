<?php

namespace ryzerbe\statssystem\hologram;

use pocketmine\utils\SingletonTrait;

class StatsHologramManager {

    use SingletonTrait;
    /** @var array  */
    public array $playerHolograms = [];
    /** @var StatsHologram[]  */
    public array $holograms = [];

    /**
     * @param StatsHologram $hologram
     */
    public function addHologram(StatsHologram $hologram): void{
        $this->holograms[$hologram->getId()] = $hologram;
    }

    /**
     * @param StatsHologram|string $hologram
     */
    public function removeHologram(StatsHologram|string $hologram): void{
        if($hologram instanceof StatsHologram) $hologram = $hologram->getId();

        unset($this->holograms[$hologram]);
    }

    public function loadHolograms(): void{
        //todo: load holograms from config
    }

    public function saveHologram(): void{
        //todo: save holos in configt
    }
}