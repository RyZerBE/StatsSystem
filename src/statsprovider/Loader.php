<?php

namespace statsprovider;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use function var_dump;

class Loader extends PluginBase {

    public function onEnable(): void {
        Server::getInstance()->getLogger()->info("Baubo, du stinkst.");

        /*
        StatsProvider::createGameTable([
            "0.0.0.0", "admin", "test1234", "ryzer"
        ], "baum", [
            "BauboStinktInt" => 2,
            "BauboStinktString" => "Ja"
        ]);*/
        var_dump(StatsProvider::getRanking([
            "0.0.0.0", "admin", "test1234", "ryzer"
        ], "baum", "BauboStinktInt", 5));
    }
}