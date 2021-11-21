<?php

namespace ryzerbe\statssystem\hologram;

use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use ryzerbe\core\entity\Hologram;
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
    /** @var int  */
    public int $entityId = -1;
    /** @var string  */
    private string $group;

    /**
     * StatsHologram constructor.
     * @param Position $position
     * @param string $category
     * @param string $title
     * @param string $group
     */
    public function __construct(Position $position, string $category, string $title, string $group){
        $this->position = $position;
        $this->category = $category;
        $this->title = $title;
        $this->id = uniqid();
        $this->group = $group;
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
     * @return string
     */
    public function getGroup(): string{
        return $this->group;
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
        if(!isset(StatsHologramManager::getInstance()->playerHolograms[$player->getName()])) StatsHologramManager::getInstance()->playerHolograms[$player->getName()] = [];
        if(in_array($this, StatsHologramManager::getInstance()->playerHolograms[$player->getName()])) return;
        $hologram = new Hologram($this->position->asVector3(), TextFormat::YELLOW."Loading hologram...");
        $this->position->getLevel()->addParticle($hologram, [$player]);
        $this->entityId = $hologram->getEntityId();
        StatsHologramManager::getInstance()->playerHolograms[$player->getName()][] = $this;
        StatsHologramManager::getInstance()->addActiveStatsHologram($this);
        $this->needUpdate = true;
    }

    public function display(): void{
        $server = Server::getInstance();
        if(in_array("ALL", $this->displayTo)){
            foreach($server->getOnlinePlayers() as $player){
                $this->displayPlayer($player);
            }
        }else{
            foreach($this->displayTo as $playerName){
                $player = $server->getPlayerExact($playerName);
                if($player === null) continue;
                if(in_array($this, StatsHologramManager::getInstance()->playerHolograms[$player->getName()])) continue;
                $this->displayPlayer($player);
            }
        }
        $this->needUpdate = true;
    }

    public function onUpdate(): void{
        $this->needUpdate = false;
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
            $this->displayPlayer($player);
        }
    }
}