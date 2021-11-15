<?php

namespace ryzerbe\statssystem\command;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ryzerbe\core\RyZerBE;
use ryzerbe\statssystem\form\holo\CreateStatsHoloForm;
use ryzerbe\statssystem\form\SelectPlayerForm;
use ryzerbe\statssystem\hologram\type\PlayerStatsHologram;
use ryzerbe\statssystem\hologram\type\TopEntriesHologram;
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
                    $form = new SimpleForm(function(Player $player, $data): void{
                        if($data === null) return;

                        switch($data) {
                            case "playerstats":
                                CreateStatsHoloForm::open($player, ["type" => PlayerStatsHologram::NETWORK_ID]);
                                break;
                            case "top_x":
                                CreateStatsHoloForm::open($player, ["type" => TopEntriesHologram::NETWORK_ID]);
                                break;
                        }
                    });
                    $form->setTitle(TextFormat::GOLD."Hologram Type");
                    $form->addButton("Playerstats", -1, "", "playerstats");
                    $form->addButton("Top X", -1, "", "top_x");
                    $form->sendToPlayer($sender);
                    break;
                case "deleteholo":
                    //todo
                    break;
                case "editholo":
                    //todo:
                    break;
                default:
                    $sender->sendMessage(RyZerBE::PREFIX.TextFormat::RED."/stats createholo <identifier:string> <category:string> <title:string> <orderBy:string> <limit:string>");
                    $sender->sendMessage(RyZerBE::PREFIX.TextFormat::RED."/stats deleteholo <identifier:string>");
                    $sender->sendMessage(RyZerBE::PREFIX.TextFormat::RED."/stats editholo <identifier:string>");
                    break;
            }
            return;
        }
        SelectPlayerForm::open($sender);
    }
}