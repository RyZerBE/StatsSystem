<?php

namespace HimmelKreis4865\StatsSystem\forms;

use HimmelKreis4865\StatsSystem\libs\pmforms\MenuForm;
use HimmelKreis4865\StatsSystem\libs\pmforms\MenuOption;
use HimmelKreis4865\StatsSystem\StatsSystem;
use HimmelKreis4865\StatsSystem\utils\AsyncUtils;
use HimmelKreis4865\StatsSystem\utils\StackedPlayerStatistics;
use pocketmine\Player;

class StatsBaseForm extends MenuForm {

	public function __construct(Player $player) {
		parent::__construct("Statistics", "", [ new MenuOption("Your statistics"), new MenuOption("Search player") ], function (Player $player, int $selectedOption): void {
			switch ($selectedOption) {
				case 0:
					AsyncUtils::getStatistics($player->getName(), function (?StackedPlayerStatistics $statistics) use ($player) : void {
						if ($player === null or !$player->isConnected()) return;
						
						if ($statistics === null) {
							$player->sendMessage(StatsSystem::PREFIX . "You don't have any statistics yet!");
							return;
						}
						$player->sendForm(new StatsViewForm($player, $statistics));
					});
					break;
					
				case 1:
					$player->sendForm(new StatsSearchForm($player));
					break;
			}
		});
	}
}