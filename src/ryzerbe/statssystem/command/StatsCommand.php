<?php

namespace ryzerbe\statssystem\command;

use baubolp\core\Ryzer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ryzerbe\statssystem\form\holo\CreateStatsHoloForm;
use ryzerbe\statssystem\form\SelectPlayerForm;
use function count;

class StatsCommand extends Command {

    /**
     * StatsCommand constructor.
     */
    public function __construct(){
        parent::__construct("statistic", "view your stats", "", ["stats"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void{
        if(!$sender instanceof Player) return;

        if(count($args) > 0 && $sender->hasPermission("stats.admin")) {

            switch($args[0]) {
                case "createholo":
                    CreateStatsHoloForm::open($sender);
                    break;
                case "deleteholo":
                    //todo
                    break;
                case "editholo":
                    //todo:
                    break;
                default:
                    $sender->sendMessage(Ryzer::PREFIX.TextFormat::RED."/stats createholo <identifier:string> <category:string> <title:string> <orderBy:string> <limit:string>");
                    $sender->sendMessage(Ryzer::PREFIX.TextFormat::RED."/stats deleteholo <identifier:string>");
                    $sender->sendMessage(Ryzer::PREFIX.TextFormat::RED."/stats editholo <identifier:string>");
                    break;
            }
            return;
        }
        SelectPlayerForm::open($sender);
    }
}