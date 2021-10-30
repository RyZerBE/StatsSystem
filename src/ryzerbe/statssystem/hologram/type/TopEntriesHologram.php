<?php

namespace ryzerbe\statssystem\hologram\type;

use baubolp\core\Ryzer;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use ryzerbe\statssystem\hologram\StatsHologram;
use ryzerbe\statssystem\provider\StatsAsyncProvider;
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
            array_walk($topEntries, function(&$v, $k): void{
                $v = TextFormat::GREEN.$k.TextFormat::DARK_GRAY." » ".TextFormat::YELLOW.$v;
            });
            if(in_array("ALL", $displayTo)){
                foreach(Server::getInstance()->getOnlinePlayers() as $player){
                    Ryzer::renameEntity($hologramId, implode("\n", $topEntries), $title, [$player]);
                }
            }else{
                foreach($displayTo as $playerName){
                    $player = Server::getInstance()->getPlayerExact($playerName);
                    if($player === null) return;

                    Ryzer::renameEntity($hologramId, $title."\n".implode("\n", $topEntries), "", [$player]);
                }
            }
        }, $this->limit, $this->sortOrder);
    }
}