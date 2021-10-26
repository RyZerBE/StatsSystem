<?php

namespace ryzerbe\statssystem\form;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SelectPlayerForm extends StatsForm {

    /**
     * @param Player $player
     * @param array $extraData
     */
    public static function open(Player $player, array $extraData = []): void{
        $form = new CustomForm(function(Player $player, $data): void{
            if($data === null) return;

            $playerName = $data["name"];
            SelectGameForm::open($player, ["player" => $playerName]);
        });
        $form->addInput(TextFormat::GOLD."Name of Player", "", $player->getName(), "name");
        $form->sendToPlayer($player);
    }
}