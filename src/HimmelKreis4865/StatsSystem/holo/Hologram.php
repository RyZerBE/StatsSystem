<?php

namespace HimmelKreis4865\StatsSystem\holo;

use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use function array_merge;
use function implode;
use function strtolower;
use function ucfirst;

class Hologram {
	
	/** @var FloatingTextParticle $particle */
	public $particle;
	
	/** @var string $levelName */
	protected $levelName;
	
	/**
	 * Hologram constructor.
	 *
	 * @param Position $position
	 * @param string $title
	 * @param string $text
	 */
	public function __construct(Position $position, string $title, string $text = "") {
		$this->levelName = $position->getLevelNonNull()->getFolderName();
		$this->particle = new FloatingTextParticle($position, $text, $title);
	}
	
	/**
	 * @param string $title
	 */
	public function setTitle(string $title): void {
		$this->particle->setTitle($title);
	}
	
	/**
	 * @param string $text
	 */
	public function setText(string $text): void {
		$this->particle->setText($text);
	}
	
	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->particle->getTitle();
	}
	
	/**
	 * @return string
	 */
	public function getText(): string {
		return $this->particle->getText();
	}
	
	/**
	 * @return bool
	 */
	public function isInvisible() : bool{
		return $this->particle->isInvisible();
	}
	
	/**
	 * @param bool $value
	 */
	public function setInvisible(bool $value = true) : void{
		$this->particle->setInvisible($value);
	}
	
	/**
	 * @param Player $player
	 */
	public function spawnTo(Player $player) {
		foreach ($this->particle->encode() as $pk) {
			$player->sendDataPacket($pk);
		}
	}
	
	
	/**
	 * @return FloatingTextParticle
	 */
	public function getParticle(): FloatingTextParticle {
		return $this->particle;
	}
	
	/**
	 * @return string
	 */
	public function getLevelName(): string {
		return $this->levelName;
	}
	
	/**
	 * Sends the hologram to a player or updates it
	 *
	 * @api
	 *
	 * @param Player $player
	 * @param Vector3 $vector3
	 * @param string $title
	 * @param string $text
	 */
	public static function sendHologramToPlayer(Player $player, Vector3 $vector3, string $title, string $text = "") {
		foreach ((new FloatingTextParticle($vector3, $title, $text))->encode() as $pk) {
			$player->sendDataPacket($pk);
		}
	}
	
	/**
	 * @return array
	 */
	public function asArray(): array {
		return [
			"levelName" => $this->getLevelName(),
			"position" => ["x" => $this->getParticle()->getFloorX(), "y" => $this->getParticle()->getFloorY(), "z" => $this->getParticle()->getFloorZ()]
		];
	}
}