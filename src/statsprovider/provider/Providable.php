<?php

namespace statsprovider\provider;

interface Providable {
	
	/**
	 * Creates a dynamic game table
	 *
	 * @api
	 *
	 * @param array $db
	 * @param string $table
	 * @param array $stats
	 *
	 * @return void
	 */
	public static function createGameTable(array $db, string $table, array $stats): void;
	
	/**
	 * Returns the ranking for the top amount of entries by a key
	 *
	 * @api
	 *
	 * @param array $db
	 * @param string $table
	 * @param string $key
	 * @param int $amount
	 *
	 * @return array
	 */
	public static function getRanking(array $db, string $table, string $key, int $amount): array;
}