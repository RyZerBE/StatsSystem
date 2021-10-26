<?php

namespace ryzerbe\statssystem\form\holo;

use baubolp\core\provider\AsyncExecutor;
use baubolp\core\Ryzer;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use mysqli;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use ryzerbe\statssystem\form\StatsForm;
use ryzerbe\statssystem\provider\StatsProvider;
use ryzerbe\statssystem\StatsSystem;
use function count;

class CreateStatsHoloForm extends StatsForm {

    /**
     * @param Player $player
     * @param array $extraData
     */
    public static function open(Player $player, array $extraData = []): void{
        $playerName = $player->getName();
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli): array{
            return StatsProvider::getCategories($mysqli);
        }, function(Server $server, array $categories) use ($playerName): void{
            $player = $server->getPlayerExact($playerName);
            if($player === null) return;
            if(count($categories) <= 0) {
                $player->sendMessage(Ryzer::PREFIX.TextFormat::RED."Es wurden keine Kategorien erstellt..");
                return;
            }


            $form = new SimpleForm(function(Player $player, $data): void{
                if($data === null) return;

                $playerName = $player->getName();
                AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli) use ($data): array{
                    return StatsProvider::getColumnsOfCategory($mysqli, $data);
                }, function(Server $server, array $columns) use ($playerName): void{
                    $player = $server->getPlayerExact($playerName);
                    $limits = [5, 10, 15];
                    if($player === null) return;
                    $form = new CustomForm(function(Player $player, $data): void{
                        if($data === null) return;

                        //todo: create hologram, cache and save into a config..
                    });
                    $form->setTitle("Create top hologram");
                    $form->addInput(TextFormat::GOLD."Title");
                    $form->addDropdown(TextFormat::GOLD."Sort by", $columns, null, "sortBy");
                    $form->addStepSlider(TextFormat::GOLD."length of top list", $limits, -1, "top");
                    $form->addDropdown(TextFormat::GOLD."Sort by", ["DESC", "ASC"], null, "sortOrder");
                    $form->sendToPlayer($player);
                });
            });

            foreach($categories as $category) $form->addButton(TextFormat::GOLD.$category, -1 ,"", $category);
            $form->sendToPlayer($player);
        });
    }
}