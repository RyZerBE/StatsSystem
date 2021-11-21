<?php

namespace ryzerbe\statssystem\form\holo;

use BauboLP\Cloud\Provider\CloudProvider;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use mysqli;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use ryzerbe\core\RyZerBE;
use ryzerbe\core\util\async\AsyncExecutor;
use ryzerbe\statssystem\form\StatsForm;
use ryzerbe\statssystem\hologram\StatsHologramManager;
use ryzerbe\statssystem\hologram\type\PlayerStatsHologram;
use ryzerbe\statssystem\hologram\type\TopEntriesHologram;
use ryzerbe\statssystem\provider\StatsProvider;
use ryzerbe\statssystem\StatsSystem;
use function count;
use function explode;

class CreateStatsHoloForm extends StatsForm {
    public static function open(Player $player, array $extraData = []): void{
        $playerName = $player->getName();
        $type = $extraData["type"];
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli): array{
            return StatsProvider::getCategories($mysqli);
        }, function(Server $server, array $categories) use ($playerName, $type): void{
            $player = $server->getPlayerExact($playerName);
            if($player === null) return;
            if(count($categories) <= 0) {
                $player->sendMessage(RyZerBE::PREFIX.TextFormat::RED."Es wurden keine Kategorien erstellt..");
                return;
            }

            switch($type) {
                case PlayerStatsHologram::NETWORK_ID:
                    $form = new SimpleForm(function(Player $player, $data): void{
                        if($data === null) return;

                        $group = explode("-", CloudProvider::getServer())[0] ?? "Lobby";
                        // TEST \\
                        $hologram = new PlayerStatsHologram($player->asPosition(), $data, TextFormat::GREEN."Your ".TextFormat::GOLD.$data.TextFormat::GREEN." statistics:", $group);
                        $hologram->playerName = $player->getName();
                        $hologram->displayTo([$player->getName()]);
                        $hologram->display();
                        StatsHologramManager::getInstance()->createHologram($hologram, $player->asLocation(), $group);
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
                            $limits = ["3", "5", "10", "15"];
                            $sortBy = [];
                            foreach($columns as $column) {
                                $sortBy[] = $column["COLUMN_NAME"];
                            }
                            $order = ["DESC", "ASC"];
                            $form = new CustomForm(function(Player $player, $data) use ($category, $limits, $sortBy, $order): void{
                                if($data === null) return;

                                $title = $data["title"];
                                $sortBy = $sortBy[$data["sortBy"]];
                                $limit = $limits[$data["top"]];
                                $sortOrder = $order[$data["sortOrder"]];
                                // TEST \\
                                $group = explode("-", CloudProvider::getServer())[0] ?? "Lobby";
                                $hologram = new TopEntriesHologram($player->asPosition(), $category, $title, $group);
                                $hologram->column = $sortBy;
                                $hologram->limit = $limit;
                                $hologram->sortOrder = $sortOrder;
                                $hologram->displayToAll();
                                $hologram->display();
                                StatsHologramManager::getInstance()->createHologram($hologram, $player->asLocation(), explode("-", CloudProvider::getServer())[0] ?? "Lobby", [
                                    "sortBy" => $sortBy,
                                    "limit" => $limit,
                                    "sortOrder" => $sortOrder
                                ]);
                                $player->sendMessage("Hologram erstellt.");
                            });
                            $form->setTitle("Create top hologram");

                            $form->addInput(TextFormat::GOLD."Title", "", "", "title");
                            $form->addDropdown(TextFormat::GOLD."Sort by", $sortBy, null, "sortBy");
                            $form->addStepSlider(TextFormat::GOLD."length of top list", $limits, -1,"top");
                            $form->addDropdown(TextFormat::GOLD."Sort order", $order, null,"sortOrder");
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