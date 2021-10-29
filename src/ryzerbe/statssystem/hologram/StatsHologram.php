<?php

namespace ryzerbe\statssystem\hologram;

use baubolp\core\entity\HoloGram;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use function in_array;
use function uniqid;

abstract class StatsHologram {

    const NETWORK_ID = -1;
    
    /** @var string  */
    private string $id;
    /** @var string  */
    private string $title;
    /** @var string[] */
    private array $displayTo = [];
    /** @var Position */
    private Position $position;
    /** @var string  */
    private string $category;
    /** @var bool  */
    public bool $needUpdate = false;

    /**
     * StatsHologram constructor.
     * @param Position $position
     * @param string $category
     * @param string $title
     */
    public function __construct(Position $position, string $category, string $title){
        $this->position = $position;
        $this->category = $category;
        $this->title = $title;
        $this->id = uniqid();
    }

    /**
     * @return string
     */
    public function getId(): string{
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string{
        return $this->title;
    }

    /**
     * @param string[] $players
     */
    public function displayTo(array $players): void{
        $this->displayTo = $players;
    }

    public function displayToAll(): void{
        $this->displayTo = ["ALL"];
    }

    /**
     * @return string[]
     */
    public function getDisplayTo(): array{
        return $this->displayTo;
    }

    /**
     * @return string
     */
    public function getCategory(): string{
        return $this->category;
    }

    public function displayPlayer(Player $player){
        if(isset(StatsHologramManager::getInstance()->playerHolograms[$player->getName()])) return;
        $hologram = new HoloGram($this->position->asVector3(), "");
        $this->position->getLevel()->addParticle($hologram, [$player]);
        StatsHologramManager::getInstance()->playerHolograms[$player->getName()] = $hologram->getEntityId();
    }

    public function display(): void{
        $server = Server::getInstance();
        if(in_array("ALL", $this->displayTo)){
            foreach($server->getOnlinePlayers() as $player){
                if(isset(StatsHologramManager::getInstance()->playerHolograms[$player->getName()])) continue;
                $hologram = new HoloGram($this->position->asVector3(), "");
                $this->position->getLevel()->addParticle($hologram, [$player]);
                StatsHologramManager::getInstance()->playerHolograms[$player->getName()] = $hologram->getEntityId();
            }
        }else{
            foreach($this->displayTo as $playerName){
                $player = $server->getPlayerExact($playerName);
                if($player === null) continue;
                if(isset(StatsHologramManager::getInstance()->playerHolograms[$player->getName()])) continue;
                $hologram = new HoloGram($this->position->asVector3(), "");
                $this->position->getLevel()->addParticle($hologram, [$player]);
                StatsHologramManager::getInstance()->playerHolograms[$player->getName()] = $hologram->getEntityId();
            }
        }
        $this->needUpdate = true;
    }

    public function onUpdate(): void{
        $this->needUpdate = false;
        foreach(Server::getInstance()->getOnlinePlayers() as $player) {
            $id = StatsHologramManager::getInstance()->playerHolograms[$player->getName()] ?? null;
            if($id !== null) continue;

            $this->displayPlayer($player);
        }

        foreach(StatsHologramManager::getInstance()->playerHolograms as $playerName => $id) {
            $player = Server::getInstance()->getPlayerExact($playerName);
            if($player !== null) continue;

            $hologram = Server::getInstance()->findEntity($id);
            $hologram?->flagForDespawn();
            unset(StatsHologramManager::getInstance()->playerHolograms[$playerName]);
        }
    }
}