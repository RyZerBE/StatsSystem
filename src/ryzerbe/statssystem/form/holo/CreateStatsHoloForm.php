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
use ryzerbe\statssystem\hologram\StatsHologramManager;
use ryzerbe\statssystem\hologram\type\PlayerStatsHologram;
use ryzerbe\statssystem\hologram\type\TopEntriesHologram;
use ryzerbe\statssystem\provider\StatsProvider;
use ryzerbe\statssystem\StatsSystem;
use function array_values;
use function array_walk;
use function count;
use function var_dump;

class CreateStatsHoloForm extends StatsForm {

    /**
     * @param Player $player
     * @param array $extraData
     */
    public static function open(Player $player, array $extraData = []): void{
        $playerName = $player->getName();
        $type = $extraData["type"];
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli): array{
            return StatsProvider::getCategories($mysqli);
        }, function(Server $server, array $categories) use ($playerName, $type): void{
            $player = $server->getPlayerExact($playerName);
            if($player === null) return;
            if(count($categories) <= 0) {
                $player->sendMessage(Ryzer::PREFIX.TextFormat::RED."Es wurden keine Kategorien erstellt..");
                return;
            }

            switch($type) {
                case PlayerStatsHologram::NETWORK_ID:
                    $form = new SimpleForm(function(Player $player, $data): void{
                        if($data === null) return;

                        // TEST \\
                        $hologram = new PlayerStatsHologram($player->asPosition(), $data, TextFormat::GREEN."Your ".TextFormat::GOLD.$data.TextFormat::GREEN." statistics:");
                        $hologram->playerName = $player->getName();
                        $hologram->displayTo([$player->getName()]);
                        $hologram->display();
                        StatsHologramManager::getInstance()->addHologram($hologram);
                        $player->sendMessage("Hologram erstellt.");
                    });

                    $form->setTitle(TextFormat::GOLD."Hologram for Gamemode?");
                    foreach($categories as $category) $form->addButton(TextFormat::GOLD.$category, -1 ,"", $category);
                    $form->sendToPlayer($player);
                    break;
                case TopEntriesHologram::NETWORK_ID:
                    $form = new SimpleForm(function(Player $player, $data): void{
                        if($data === null) return;

                        $playerName = $player->getName();
                        $category = $data;
                        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli) use ($data): array{
                            return StatsProvider::getColumnsOfCategory($mysqli, $data);
                        }, function(Server $server, array $columns) use ($playerName, $category): void{
                            $player = $server->getPlayerExact($playerName);
                            if($player === null) return;
                            $form = new CustomForm(function(Player $player, $data) use ($category): void{
                                if($data === null) return;

                                $title = $data["title"];
                                $sortBy = $data["sortBy"];
                                $limit = $data["top"];
                                $sortOrder = $data["sortOrder"];
                                // TEST \\
                                $hologram = new TopEntriesHologram($player->asPosition(), $category, $title);
                                $hologram->column = $sortBy;
                                $hologram->limit = $limit;
                                $hologram->sortOrder = $sortOrder;
                                $hologram->displayToAll();
                                $hologram->display();
                                StatsHologramManager::getInstance()->addHologram($hologram);
                                $player->sendMessage("Hologram erstellt.");
                            });
                            $form->setTitle("Create top hologram");

                            $sortBy = [];
                            foreach($columns as $column) {
                                $sortBy[] = $column["COLUMN_NAME"];
                            }
                            $form->addInput(TextFormat::GOLD."Title", "", "", "title");
                            $form->addDropdown(TextFormat::GOLD."Sort by", $sortBy, null, "sortBy");
                            $form->addStepSlider(TextFormat::GOLD."length of top list", ["3", "5", "10", "15"], -1,"top");
                            $form->addDropdown(TextFormat::GOLD."Sort by", ["DESC", "ASC"], null,"sortOrder");
                            $form->sendToPlayer($player);
                        });
                    });

                    $form->setTitle(TextFormat::GOLD."Hologram for Gamemode?");
                    foreach($categories as $category) $form->addButton(TextFormat::GOLD.$category, -1 ,"", $category);
                    $form->sendToPlayer($player);
                    break;
            }
        });
    }
}