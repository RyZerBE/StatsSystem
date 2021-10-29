<?php

namespace ryzerbe\statssystem\form;

use baubolp\core\provider\AsyncExecutor;
use baubolp\core\provider\LanguageProvider;
use jojoe77777\FormAPI\SimpleForm;
use mysqli;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use ryzerbe\statssystem\provider\StatsProvider;
use ryzerbe\statssystem\StatsSystem;
use function date;
use function str_starts_with;
use function substr;
use function time;
use function var_dump;

class StatsResultForm extends StatsForm {

    /**
     * @param Player $player
     * @param array $extraData
     */
    public static function open(Player $player, array $extraData = []): void{
        $playerName = $extraData["player"];
        $senderName = $extraData["sender"];
        $category = $extraData["category"];
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli) use ($playerName, $category): ?array{
            return StatsProvider::getStatistics($mysqli, $playerName, $category);
        }, function(Server $server, ?array $statistics) use ($playerName, $category, $senderName){
            $player = $server->getPlayerExact($senderName);
            if($player === null) return;

            $form = new SimpleForm(function(Player $player, $data): void{
                if($data === null) return;

                SelectPlayerForm::open($player);
            });
            $form->setTitle(TextFormat::GOLD.$category);
            $form->addButton(TextFormat::RED."🡐 Back", -1, "", "back");
            if($statistics === null){
                $form->setContent(LanguageProvider::getMessageContainer("no-stats", $senderName, ["#game" => $category]));
                $form->sendToPlayer($player);
                return;
            }

            $monthly = []; $alltime = [];

            foreach($statistics as $k => $v){
                if($k === "date") continue;
                if($k === "player") continue;
                if(str_starts_with($k, "m_")){
                    $monthly[substr($k, 2)] = $v;
                }else{
                    $alltime[$k] = $v;
                }
            }

            $content = "§l§6Monthly stats§r §8(§b".date("F", time())."§8)";
            array_walk($monthly, function(&$v, $k): void{
                $v = $k.": §7".$v;
            });
            $content .= "\n\n§7» §f".implode("\n §7» §b", $monthly);

            $content .= "\n\n§l§6Alltime stats§r";
            array_walk($alltime, function(&$v, $k): void{
                $v = $k.": §7".$v;
            });
            $content .= "\n\n§7» §f".implode("\n §7» §b", $alltime);

            $form->setContent($content);
            $form->sendToPlayer($player);
        });
    }
}