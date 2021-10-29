<?php

namespace ryzerbe\statssystem\hologram;

use pocketmine\scheduler\Task;
use pocketmine\Server;

class HologramUpdateTask extends Task {

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick){
        foreach(StatsHologramManager::getInstance()->holograms as $hologram) {
            if($currentTick % (20 * 30) !== 0 && !$hologram->needUpdate) continue;
            $hologram->onUpdate();
        }
    }
}