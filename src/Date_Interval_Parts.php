<?php declare( strict_types=1 );

namespace Sirbrillig;

/**
 * A record class for `get_interval_parts_from_renew_interval()`.
 *
 * Useful for turning an interval like `1 year` into arguments for
 * `apply_interval_to_timestamp()`.
 *
 * Can also be used for `apply_interval_parts_to_timestamp()`.
 */
class Date_Interval_Parts {
	/**
	 * A value that can be passed to `apply_interval_to_timestamp()`.
	 *
	 * eg: `year`
	 */
	public string $interval_unit;

	/**
	 * A count that can be passed to `apply_interval_to_timestamp()`.
	 */
	public int $interval_count;

	public static function from(
		int $interval_count,
		string $interval_unit,
	): self {
		$parts                 = new self();
		$parts->interval_count = $interval_count;
		$parts->interval_unit  = $interval_unit;
		return $parts;
	}
}
