<?php

namespace HimmelKreis4865\StatsSystem\utils;

final class Utils {
	
	/**
	 * Filters all keys from the array that are equivalent to null
	 *
	 * @api
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function filterNullables(array $array): array {
		foreach ($array as $k => $v) {
			if ($v === null) unset($array[$k]);
		}
		return $array;
	}
}