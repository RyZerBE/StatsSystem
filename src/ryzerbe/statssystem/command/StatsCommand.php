<?php

namespace ryzerbe\statssystem\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use ryzerbe\statssystem\form\SelectPlayerForm;

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

        SelectPlayerForm::open($sender);
    }
}