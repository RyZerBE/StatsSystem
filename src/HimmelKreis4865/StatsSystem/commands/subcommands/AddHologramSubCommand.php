<?php

namespace HimmelKreis4865\StatsSystem\commands\subcommands;

use HimmelKreis4865\StatsSystem\holo\StatsHologram;
use HimmelKreis4865\StatsSystem\StatsSystem;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use function in_array;
use function strtoupper;

class AddHologramSubCommand extends SubCommand
{

    public function __construct()
    {
        parent::__construct("addhologram");
    }

    public function execute(CommandSender $sender, array $args)
    {
        if (!$sender instanceof Player) return;
        if (!isset($args[0]) || !isset($args[1])) {
            $sender->sendMessage(StatsSystem::PREFIX . "Usage: §f/stats addhologram <category: string> <statistic: string> [sortOrder: string (ASC|DESC)] [title: string]");
            return;
        }

        $category = $args[0];
        unset($args[0]);
        $statistic = $args[1];
        unset($args[1]);

        $sort = "DESC";
        $title = null;
        if(isset($args[2])) {
            $sort = (in_array(strtoupper($args[2]), ["ASC", "DESC"]) ? strtoupper($args[2]) : "DESC");
            unset($args[2]);
        }


        if (count($args) > 0)
            $title = implode(" ", $args);
        $hologram = new StatsHologram($sender->asPosition(), $category, $statistic, $sort, $title);
        foreach ($sender->getLevelNonNull()->getPlayers() as $player) {
            $hologram->spawnTo($player);
        }
    }
}