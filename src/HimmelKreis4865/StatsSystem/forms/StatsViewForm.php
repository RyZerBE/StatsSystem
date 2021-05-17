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
use function var_dump;
use const ARRAY_FILTER_USE_KEY;

class StatsViewForm extends MenuForm {
	
	public function __construct(Player $player, StackedPlayerStatistics $statistics) {
		var_dump((array) $statistics);
		
		// todo: replace the value with the real language
		$lang = null;
		$array = array_filter((array) $statistics, function (string $k): bool {
			return in_array($k[0], str_split("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"));
		}, ARRAY_FILTER_USE_KEY);

		array_walk($array, function (&$v, $k) use ($lang) {
			var_dump([$k, $v]);
			// anything between /**/ is raw code - the value in the language file should be smth like "beds" => "Destroyed beds"   [array key is the database column]
			$v = $k . "::" . TextFormat::GOLD . /* $lang->translate("StatsSystem.keys." . $k) ??*/ $k . ": " . TextFormat::GRAY . $v;
			var_dump($v);
		});
		
		$content = "§l§eMonthly stats§r\n" .
				implode("\n", array_map(function ($k): string {
					return explode("::", $k)[1];
				}, array_filter(array_values($array), function($k): bool {
			return (substr(explode("::", $k)[0], 0, strlen(StatsSystem::ALLTIME_PREFIX)) !== StatsSystem::ALLTIME_PREFIX);
		})));
		
		$content .= "\n\n§l§eAlltime stats§r\n" .
			implode("\n", array_map(function ($k): string {
				return str_replace(StatsSystem::ALLTIME_PREFIX, "", explode("::", $k)[1]);
			}, array_filter(array_values($array), function($k): bool {
				return (substr(explode("::", $k)[0], 0, strlen(StatsSystem::ALLTIME_PREFIX)) === StatsSystem::ALLTIME_PREFIX);
			})));
		
		var_dump($array, $content);
		
		parent::__construct($statistics->getOwner() . "'s statistics", $content, [ new MenuOption("Back") ], function (Player $player, int $selectedOption): void {
			$player->sendForm(new StatsBaseForm($player));
		});
	}
}