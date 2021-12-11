<?php

namespace ryzerbe\statssystem\hologram;

use pocketmine\scheduler\Task;

class HologramUpdateTask extends Task {
    public function onRun(int $currentTick){
        foreach(StatsHologramManager::getInstance()->activeStatsHolograms as $hologram) {
            if(($currentTick % (20 * 15) !== 0 || !$hologram->needUpdate) && ($currentTick % (20 * 45) !== 0)) continue;
            $hologram->onUpdate();
        }
    }
}