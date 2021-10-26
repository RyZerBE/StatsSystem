<?php

namespace ryzerbe\statssystem\form;

use pocketmine\Player;

abstract class StatsForm {

    /**
     * @param Player $player
     * @param array $extraData
     */
    abstract public static function open(Player $player, array $extraData = []): void;
}