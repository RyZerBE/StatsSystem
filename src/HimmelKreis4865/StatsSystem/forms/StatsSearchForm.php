<?php

namespace HimmelKreis4865\StatsSystem\forms;

use HimmelKreis4865\StatsSystem\libs\pmforms\CustomForm;
use HimmelKreis4865\StatsSystem\libs\pmforms\CustomFormResponse;
use HimmelKreis4865\StatsSystem\libs\pmforms\element\Input;
use HimmelKreis4865\StatsSystem\StatsSystem;
use HimmelKreis4865\StatsSystem\utils\AsyncUtils;
use pocketmine\Player;
use function count;

class StatsSearchForm extends CustomForm {
	
	public function __construct(Player $player) {
		parent::__construct("Search player", [new Input("search", "Enter a playername to search for", "Username...")], function (Player $player, CustomFormResponse $data): void {
			AsyncUtils::getAllStatistics($data->getString("search"), function (array $statistics) use ($player) : void {
				if ($player === null or !$player->isConnected()) return;
				
				if (!count($statistics)) {
					$player->sendMessage(StatsSystem::PREFIX . "The given player was not found");
					return;
				}
				$player->sendForm(new StatsViewForm($player, $statistics));
			});
		});
	}
}