<?php

namespace HimmelKreis4865\StatsSystem\forms;

use HimmelKreis4865\StatsSystem\libs\pmforms\MenuForm;
use HimmelKreis4865\StatsSystem\libs\pmforms\MenuOption;
use HimmelKreis4865\StatsSystem\StatsSystem;
use HimmelKreis4865\StatsSystem\utils\StackedPlayerStatistics;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use function array_filter;
use function array_map;
use function array_values;
use function array_walk;
use function explode;
use function implode;
use function in_array;
use function str_replace;
use function str_split;
use function strlen;
use function substr;
use const ARRAY_FILTER_USE_KEY;

class StatsViewForm extends MenuForm {
	/**
	 * StatsViewForm constructor.
	 *
	 * @param Player $player
	 * @param StackedPlayerStatistics[] $categories
	 */
	public function __construct(Player $player, array $categories) {
		// todo: replace the value with the real language
		$lang = null;
		
		/** @var StackedPlayerStatistics $monthly */
		$monthly = [];
		/** @var StackedPlayerStatistics $alltime */
		$alltime = [];
		
		$owner = null;
		
		foreach ($categories as $name => $statistics) {
			$owner = $statistics->getOwner();
			foreach ($statistics as $k => $v) {
				if (substr($k, 0, 2) === "m_") {
					$monthly[$name][substr($k, 2)] = $v;
				} else {
					$alltime[$name][$k] = $v;
				}
			}
		}
		
		$content = "§l§6Monthly stats§r";
		foreach ($monthly as $category => $stats) {
			$obj = (array) $stats;
			array_walk($obj, function (&$v, $k): void { $v = $k . ": §7" . $v; });
			$content .= "\n\n§6$category:\n §7» §b" . implode("\n §7» §b", $obj);
		}
		$content .= "\n\n§l§6Alltime stats§r";
		foreach ($alltime as $category => $stats) {
			$obj = (array) $stats;
			array_walk($obj, function (&$v, $k): void { $v = $k . ": §7" . $v; });
			$content .= "\n\n§6$category:\n §7» §b" . implode("\n §7» §b", $obj);
		}
		
		parent::__construct($owner . "'s statistics", $content, [ new MenuOption("Back") ], function (Player $player, int $selectedOption): void {
			$player->sendForm(new StatsBaseForm($player));
		});
	}
}