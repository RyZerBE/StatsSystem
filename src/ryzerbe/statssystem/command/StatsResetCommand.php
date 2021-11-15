<?php

namespace ryzerbe\statssystem\command;

use jojoe77777\FormAPI\SimpleForm;
use mysqli;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use ryzerbe\core\language\LanguageProvider;
use ryzerbe\core\RyZerBE;
use ryzerbe\core\util\async\AsyncExecutor;
use ryzerbe\statssystem\provider\StatsAsyncProvider;
use ryzerbe\statssystem\provider\StatsProvider;
use ryzerbe\statssystem\StatsSystem;
use function is_numeric;
use function is_string;

class StatsResetCommand extends Command {

    public function __construct(){
        parent::__construct("statsreset", "reset your stats in specific category", "", []);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if($sender->hasPermission("stats.admin") && isset($args[0])){
            if(isset($args[1]) && isset($args[2])){
                $name = $args[1];
                $count = $args[2];
                if(!is_numeric($count)) return;
                if(!is_string($name)) return;

                switch($args[0]){
                    case "add":
                        AsyncExecutor::submitMySQLAsyncTask("RyzerCore", function(mysqli $mysqli) use ($name, $count): void{
                            $mysqli->query("INSERT INTO `statstokens`(player, `tokens`) VALUES ('$name', '$count') ON DUPLICATE KEY UPDATE `tokens` = tokens+$count");
                        });
                        $sender->sendMessage(RyZerBE::PREFIX.TextFormat::GREEN."Der Spieler $name hat ".TextFormat::AQUA.$count." Statsreset-Tokens ".TextFormat::GREEN."erhalten.");
                        break;
                    case "remove":
                        AsyncExecutor::submitMySQLAsyncTask("RyzerCore", function(mysqli $mysqli) use ($name, $count): void{
                            $mysqli->query("INSERT INTO `statstokens`(player, `tokens`) VALUES ('$name', '0') ON DUPLICATE KEY UPDATE `tokens` = tokens-$count");
                        });
                        $sender->sendMessage(RyZerBE::PREFIX.TextFormat::GREEN."Dem Spieler $name wurden ".TextFormat::AQUA.$count." Statsreset-Tokens ".TextFormat::RED."entfernt.");
                        break;
                    case "set":
                        AsyncExecutor::submitMySQLAsyncTask("RyzerCore", function(mysqli $mysqli) use ($name, $count): void{
                            $mysqli->query("INSERT INTO `statstokens`(player, `tokens`) VALUES ('$name', '$count') ON DUPLICATE KEY UPDATE `tokens` = $count");
                        });
                        $sender->sendMessage(RyZerBE::PREFIX.TextFormat::GREEN."Der Spieler $name besitzt nun ".TextFormat::AQUA.$count." Statsreset-Tokens.");
                        break;
                }
            }else {
                $sender->sendMessage(RyZerBE::PREFIX.TextFormat::RED."/statsreset <add|remove|set> <Player:string> <Count:int>");
            }
            return;
        }
        iF(!$sender instanceof Player) return;
        $playerName = $sender->getName();

        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli): array{
            return StatsProvider::getCategories($mysqli);
        }, function(Server $server, array $categories) use ($playerName): void{
            $player = $server->getPlayerExact($playerName);
            if($player === null) return;
            $form = new SimpleForm(function(Player $player, $data): void{
                if($data === null) return;

                $category = $data;
                $playerName = $player->getName();
                AsyncExecutor::submitMySQLAsyncTask("RyzerCore", function(mysqli $mysqli) use ($playerName): int{
                    $res = $mysqli->query("SELECT * FROM statstokens WHERE player='$playerName'");
                    if($res->num_rows <= 0) return 0;

                    if($data = $res->fetch_assoc()) {
                        $tokens = (int)$data["tokens"];
                        if($tokens > 0) $mysqli->query("UPDATE `statstokens` SET tokens=tokens-1 WHERE player='$playerName'");
                        return $tokens;
                    }

                    return 0;
                }, function(Server $server, int $tokens) use ($playerName, $category): void{
                    $player = $server->getPlayerExact($playerName);
                    if($player === null) return;

                    if($tokens <= 0) {
                        $player->sendMessage(RyZerBE::PREFIX.LanguageProvider::getMessageContainer("no-stats-tokens", $playerName));
                        return;
                    }

                    StatsAsyncProvider::resetStatistics($playerName, $category, function(Server $server, $result) use ($playerName, $category): void{
                        $player = $server->getPlayerExact($playerName);
                        if($player === null) return;

                        $player->sendMessage(RyZerBE::PREFIX.LanguageProvider::getMessageContainer("stats-reset-success", $playerName, ["#category" => $category]));

                    });
                });
            });

            foreach($categories as $category) {
                $form->addButton(TextFormat::GOLD.$category, -1, "", $category);
            }
            $form->sendToPlayer($player);
        });
    }
}