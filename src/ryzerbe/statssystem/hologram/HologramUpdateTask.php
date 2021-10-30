<?php

namespace ryzerbe\statssystem\hologram;

use pocketmine\scheduler\Task;

class HologramUpdateTask extends Task {

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick){
        foreach(StatsHologramManager::getInstance()->activeStatsHolograms as $hologram) {
            if($currentTick % (20 * 60) !== 0 && !$hologram->needUpdate) continue;
            $hologram->onUpdate();
        }
    }
}