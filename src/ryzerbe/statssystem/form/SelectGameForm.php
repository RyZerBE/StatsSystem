<?php

namespace ryzerbe\statssystem\form;

use jojoe77777\FormAPI\SimpleForm;
use mysqli;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use ryzerbe\core\util\async\AsyncExecutor;
use ryzerbe\statssystem\provider\StatsProvider;
use ryzerbe\statssystem\StatsSystem;
use function var_dump;

class SelectGameForm extends StatsForm {

    /**
     * @param Player $player
     * @param array $extraData
     */
    public static function open(Player $player, array $extraData = []): void{
        $playerName = $player->getName();
        $getStatsPlayerName = $extraData["player"];
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli): array{
            return StatsProvider::getCategories($mysqli);
        }, function(Server $server, array $categories) use ($playerName, $extraData): void{
            $player = $server->getPlayerExact($playerName);
            if($player === null) return;
            $form = new SimpleForm(function(Player $player, $data) use ($extraData): void{
                if($data === null) return;


                $extraData["category"] = $data;
                $extraData["sender"] = $player->getName();
                StatsResultForm::open($player, $extraData);
            });

            foreach($categories as $category) {
                $form->addButton(TextFormat::GOLD.$category, -1, "", $category);
            }
            $form->sendToPlayer($player);
        });
    }
}