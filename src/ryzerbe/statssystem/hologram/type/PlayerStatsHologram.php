<?php

namespace ryzerbe\statssystem\hologram\type;

use baubolp\core\entity\HoloGram;
use baubolp\core\Ryzer;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\Server;
use ryzerbe\statssystem\hologram\StatsHologram;
use ryzerbe\statssystem\hologram\StatsHologramManager;
use ryzerbe\statssystem\provider\StatsAsyncProvider;
use function array_walk;
use function date;
use function implode;
use function str_starts_with;
use function substr;
use function time;

class PlayerStatsHologram extends StatsHologram {

    const NETWORK_ID = 0;
    /** @var string  */
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

            $content = "§l§6Monthly stats§r §8(§b".date("F", time())."§8)";
            array_walk($monthly, function(&$v, $k): void{
                $v = $k.": §b".$v;
            });
            $content .= "\n\n§7» §f".implode("\n §7» §f", $monthly);

            $content .= "\n\n§l§6Alltime stats§r";
            array_walk($alltime, function(&$v, $k): void{
                $v = $k.": §b".$v;
            });
            $content .= "\n\n§7» §f".implode("\n §7» §f", $alltime);

            $player = Server::getInstance()->getPlayerExact($playerName);
            if($player === null) return;

            Ryzer::renameEntity($id, $title."\n".$content, "", [$player]);
        });
    }
}