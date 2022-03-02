<?php

namespace ryzerbe\statssystem\form;

use jojoe77777\FormAPI\SimpleForm;
use mysqli;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use ryzerbe\core\language\LanguageProvider;
use ryzerbe\core\util\async\AsyncExecutor;
use ryzerbe\statssystem\provider\StatsProvider;
use ryzerbe\statssystem\StatsSystem;
use function date;
use function number_format;
use function str_starts_with;
use function substr;
use function time;
use function ucfirst;

class StatsResultForm extends StatsForm {
    public static function open(Player $player, array $extraData = []): void{
        $playerName = $extraData["player"];
        $senderName = $extraData["sender"];
        $category = $extraData["category"];
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli) use ($playerName, $category): ?array{
            return StatsProvider::getStatistics($mysqli, $playerName, $category);
        }, function(Server $server, ?array $statistics) use ($playerName, $category, $senderName){
            $player = $server->getPlayerExact($senderName);
            if($player === null) return;

            $form = new SimpleForm(function(Player $player, $data) use ($playerName): void{
                if($data !== "back") return;
                SelectGameForm::open($player, ["player" => $playerName]);
            });
            $form->setTitle(TextFormat::GOLD.ucfirst($category));
            $form->addButton(TextFormat::RED."⇦ Back", -1, "", "back");
            if($statistics === null){
                $form->setContent(LanguageProvider::getMessageContainer("no-stats", $senderName, ["#game" => $category]));
                $form->sendToPlayer($player);
                return;
            }

            $monthly = [];
            $alltime = [];

            foreach($statistics as $k => $v){
                if($k === "date") continue;
                if($k === "player") continue;
                if(str_starts_with($k, "m_")){
                    $monthly[substr($k, 2)] = $v;
                }else{
                    $alltime[$k] = $v;
                }
            }

            if(isset($alltime["kills"]) && isset($alltime["deaths"])) {
                if($alltime["kills"] == 0) $alltime["K/D"] = $alltime["kills"].".00";
                else if($alltime["deaths"] == 0) $alltime["K/D"] = "0.00";
                else $alltime["K/D"] = number_format((int)$alltime["kills"] / (int)$alltime["deaths"], 2);
            }

            if(isset($monthly["kills"]) && isset($monthly["deaths"])) {
                if($monthly["kills"] == 0) $monthly["K/D"] = $monthly["kills"].".00";
                else if($monthly["deaths"] == 0) $monthly["K/D"] = "0.00";
                else $monthly["K/D"] = number_format((int)$monthly["kills"] / (int)$monthly["deaths"], 2);
            }

            $content = "§l§6Monthly stats§r §8(§b".date("F", time())."§8)";
            array_walk($monthly, function(&$v, $k): void{
                $v = ucfirst($k).": §7".$v;
            });
            $content .= "\n\n §7» §f".implode("\n §7» §f", $monthly);

            $content .= "\n\n§l§6Alltime stats§r";
            array_walk($alltime, function(&$v, $k): void{
                $v = ucfirst($k).": §7".$v;
            });
            $content .= "\n\n §7» §f".implode("\n §7» §f", $alltime);

            $form->setContent($content);
            $form->sendToPlayer($player);
        });
    }
}