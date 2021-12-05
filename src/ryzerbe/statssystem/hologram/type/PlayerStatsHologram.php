<?php

namespace ryzerbe\statssystem\hologram\type;

use pocketmine\entity\DataPropertyManager;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\Server;
use ryzerbe\statssystem\hologram\StatsHologram;
use ryzerbe\statssystem\provider\StatsAsyncProvider;
use function array_walk;
use function date;
use function implode;
use function number_format;
use function str_starts_with;
use function substr;
use function time;
use function ucfirst;

class PlayerStatsHologram extends StatsHologram {
    public const NETWORK_ID = 0;

    public string $playerName = "";

    public function onUpdate(): void{
        parent::onUpdate();
        $playerName = $this->playerName;
        $id = $this->entityId;
        $title = $this->getTitle();
        StatsAsyncProvider::getStatistics($this->playerName, $this->getCategory(), function(?array $statistics) use ($id, $playerName, $title): void{
            if($statistics === null) return;
            $monthly = [];
            $alltime = [];

            foreach($statistics as $k => $v){
                if($k === "date") continue;
                if($k === "player") continue;
                if(str_starts_with($k, "m_")){
                    $monthly[substr($k, 2)] = $v;
                }else{
                    $alltime[$k] = $v;
                }
            }

            if(isset($alltime["kills"]) && isset($alltime["deaths"])) {
                if($alltime["kills"] === 0) $alltime["K/D"] = $alltime["kills"].".00";
                else if($alltime["deaths"] === 0) $alltime["K/D"] = "0.00";
                else $alltime["K/D"] = number_format((int)$alltime["kills"] / ((int)$alltime["deaths"] === 0 ? 1 : (int)$alltime["deaths"]), 2);
            }

            if(isset($monthly["kills"]) && isset($monthly["deaths"])) {
                if($monthly["kills"] == 0) $monthly["K/D"] = $monthly["kills"].".00";
                else if($monthly["deaths"] == 0) $monthly["K/D"] = "0.00";
                else $monthly["K/D"] = number_format((int)$monthly["kills"] / ((int)$monthly["deaths"] === 0 ? 1 : (int)$monthly["deaths"]), 2);
            }

            $content = "§l§6Monthly stats§r §8(§b".date("F", time())."§8)";
            array_walk($monthly, function(&$v, $k): void{
                $v = ucfirst($k).": §b".$v;
            });
            $content .= "\n\n§7» §f".implode("\n §7» §f", $monthly);

            $content .= "\n\n§l§6Alltime stats§r";
            array_walk($alltime, function(&$v, $k): void{
                $v = ucfirst($k).": §b".$v;
            });
            $content .= "\n\n§7» §f".implode("\n §7» §f", $alltime);

            $player = Server::getInstance()->getPlayerExact($playerName);
            if($player === null) return;

            $actorPacket = new SetActorDataPacket();
            $actorPacket->entityRuntimeId = $id;

            $dataPropertyManager = new DataPropertyManager();
            $dataPropertyManager->setString(Entity::DATA_NAMETAG, $title."\n".$content);
            $actorPacket->metadata = $dataPropertyManager->getAll();
            $player->dataPacket($actorPacket);
        });
    }
}