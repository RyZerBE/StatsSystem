<?php

namespace HimmelKreis4865\StatsSystem\utils;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use ReflectionFunction;
use stdClass;
use function count;
use function mt_rand;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

final class AsyncExecutor {
	
	/** @var callable[] $syncId */
	public static $syncId = [];
	
	/**
	 * Executes a snippet async and runs a sync method after
	 *
	 * @api
	 *
	 * @param callable $async
	 * @param callable|null $sync
	 * @param array $parameters
	 *
	 * @return void
	 */
	public static function execute(callable $async, ?callable $sync = null, array $parameters = []): void {
		if ($sync !== null) {
			do {
				$id = mt_rand(PHP_INT_MIN, PHP_INT_MAX);
			} while (isset(self::$syncId[$id]));
			self::$syncId[$id] = $sync;
		}
		Server::getInstance()->getAsyncPool()->submitTask(new class($async, $id ?? null, $parameters) extends AsyncTask {
			
			/** @var int|null $id */
			protected $id;
			
			/** @var callable $callable */
			protected $callable;
			
			/** @var stdClass|null $class */
			protected $class;
			
			/**
			 * Anonymous Async Task constructor.
			 *
			 * @param callable $callable
			 * @param int|null $id
			 * @param array $parameters
			 */
			public function __construct(callable $callable, ?int $id, array $parameters = []) {
				$this->callable = $callable;
				$this->id = $id;
				if (count($parameters) > 0) {
					$class = new stdClass();
					foreach ($parameters as $key => $value) {
						$class->{$key} = $value;
					}
					$this->class = $class;
				}
			}
			
			public function onRun() {
				$this->setResult((($this->class instanceof stdClass ? ($this->callable)($this->class) : ($this->callable)())));
			}
			
			public function onCompletion(Server $server) {
				if ($this->id === null) return;
				$callable = AsyncExecutor::$syncId[$this->id];
				if ((new ReflectionFunction($callable))->getNumberOfParameters() > 0) {
					$callable($this->getResult());
				} else {
					$callable();
				}
			}
		});
	}
}