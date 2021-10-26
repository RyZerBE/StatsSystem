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
            $statistics = StatsProvider::getStatistics($mysqli, $playerName, $category);
            return $statistics;
        }, function(Server $server, ?array $statistics) use ($playerName, $category, $senderName){
            $player = $server->getPlayerExact($senderName);
            if($player === null) return;

            $form = new SimpleForm(function(Player $player, $data): void{
                if($data === null) return;

                SelectPlayerForm::open($player);
            });
            $form->setTitle(TextFormat::GOLD.$category);
            $form->addButton(TextFormat::RED."Back", -1, "", "back");
            if($statistics === null) {
                $form->setContent(LanguageProvider::getMessageContainer("no-stats", $playerName, ["#game" => $category]));
                $form->sendToPlayer($player);
                return;
            }

            $monthly = []; $alltime = [];

            foreach ($statistics as $k => $v) {
                if (str_starts_with($k, "m_")) {
                    $monthly[substr($k, 2)] = $v;
                } else {
                    $alltime[$k] = $v;
                }
            }

            $content = "§l§6Monthly stats§r §8(§b".date("F", time())."§8)";
            foreach ($monthly as $stats) {
                $obj = (array) $stats;
                array_walk($obj, function (&$v, $k): void { $v = $k . ": §7" . $v; });
                $content .= "\n\n§7» §b" . implode("\n §7» §b", $obj);
            }
            $content .= "\n\n§l§6Alltime stats§r";
            foreach ($alltime as $stats) {
                $obj = (array) $stats;
                array_walk($obj, function (&$v, $k): void { $v = $k . ": §7" . $v; });
                $content .= "\n\n§7» §b" . implode("\n §7» §b", $obj);
            }

            $form->setContent($content);
            $form->sendToPlayer($player);
        });
    }
}