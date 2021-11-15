<?php

namespace ryzerbe\statssystem\hologram\type;

use baubolp\core\Ryzer;
use pocketmine\entity\DataPropertyManager;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use ryzerbe\statssystem\hologram\StatsHologram;
use ryzerbe\statssystem\provider\StatsAsyncProvider;
use function array_keys;
use function array_search;
use function array_walk;
use function implode;
use function in_array;

class TopEntriesHologram extends StatsHologram {

    const NETWORK_ID = 2;

    /** @var string */
    public string $column = "";
    /** @var int */
    public int $limit = 3;
    /** @var string */
    public string $sortOrder = "DESC";

    public function onUpdate(): void{
        parent::onUpdate();
        $title = $this->getTitle();
        $displayTo = $this->getDisplayTo();

        $hologramId = $this->entityId;
        StatsAsyncProvider::getTopEntriesOfColumn($this->getCategory(), $this->column, function(array $topEntries) use ($title, $displayTo, $hologramId): void{
            array_walk($topEntries, function(&$v, $k) use ($topEntries): void{
                $v = TextFormat::RED.(array_search($k, array_keys($topEntries)) +1).". ".TextFormat::GREEN.$k.TextFormat::DARK_GRAY." » ".TextFormat::YELLOW.$v;
            });
            if(in_array("ALL", $displayTo)){
                foreach(Server::getInstance()->getOnlinePlayers() as $player){
                    $actorPacket = new SetActorDataPacket();
                    $actorPacket->entityRuntimeId = $hologramId;

                    $dataPropertyManager = new DataPropertyManager();
                    $dataPropertyManager->setString(Entity::DATA_NAMETAG, $title."\n".implode("\n", $topEntries));
                    $actorPacket->metadata = $dataPropertyManager->getAll();
                    $player->dataPacket($actorPacket);
                }
            }else{
                foreach($displayTo as $playerName){
                    $player = Server::getInstance()->getPlayerExact($playerName);
                    if($player === null) return;

                    $actorPacket = new SetActorDataPacket();
                    $actorPacket->entityRuntimeId = $hologramId;

                    $dataPropertyManager = new DataPropertyManager();
                    $dataPropertyManager->setString(Entity::DATA_NAMETAG, $title."\n".implode("\n", $topEntries));
                    $actorPacket->metadata = $dataPropertyManager->getAll();
                    $player->dataPacket($actorPacket);
                }
            }
        }, $this->limit, $this->sortOrder);
    }
}