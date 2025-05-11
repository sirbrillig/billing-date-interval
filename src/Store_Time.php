<?php
declare( strict_types=1 );

namespace Sirbrillig;

/**
 * Class to replace uses of `time()` or `new DateTime()` that allows
 * overriding.
 *
 * This class should be used by everything in the billing system that needs to
 * know the current time instead of calling `time()` or `new DateTime()`, etc.
 *
 * Sometimes it's useful to be able to test making a purchase on a specific day
 * or time, particularly in unit tests. By using this class, it becomes
 * possible to override what the billing system thinks the current time is.
 *
 * NOTE: overriding the current time is very dangerous and should probably be
 * used only for automated testing. This will only allow overriding in a
 * sandbox or test environment.
 *
 * Usage:
 *
 * Use this to replace calls to `time()` like this:
 *
 * ```
 * $time = ( new Store_Time() )->get_timestamp();
 * ```
 *
 * There are other helper methods available to get a `DateTimeImmutable` or a
 * formatted time string if that would be more appropriate.
 *
 * The current time can be overridden using the `set_override()` method.
 *
 * The override should be a time string that can be parsed by the
 * `DateTimeImmutable` constructor, typically in the format `Y-m-d H:i:s`.
 *
 * To override the time a purchase is made, do this:
 *
 * ```
 * Store_Time::set_override( '2024-10-30 14:00:00' );
 * ```
 *
 * Make sure to call `clear_override()` when done!
 */
class Store_Time {
	/**
	 * Set this to allow Store_Time to allow overrides.
	 *
	 * NOTE: You should probably not use this mechanism in real code. Use
	 * something specific to your environment.
	 */
	public static bool $is_testing_environment = false;

	private static string|null $time_string_override = null;

	/**
	 * Make sure this is only run in automated tests or on sandbox.
	 */
	private function can_safely_override_time_string(): bool {
		if ( self::$is_testing_environment ) {
			return true;
		}
		return false;
	}

	/**
	 * Return just the overridden time string, if any.
	 */
	private function get_overridden_time_string(): string|null {
		if ( ! $this->can_safely_override_time_string() ) {
			return null;
		}
		return self::$time_string_override;
	}

	/**
	 * Override the time returned by this class.
	 *
	 * Has no effect outside a test environment.
	 *
	 * The time string is something that can be parsed by the `DateTimeImmutable`
	 * constructor, typically in the format `Y-m-d H:i:s`.
	 */
	public static function set_override( string $time_string ): void {
		self::$time_string_override = $time_string;
	}

	public static function clear_override(): void {
		self::$time_string_override = null;
	}

	/**
	 * Return true if the time is being overridden.
	 */
	public function has_override(): bool {
		return (bool) $this->get_overridden_time_string();
	}

	/**
	 * Return the current or overridden time as a `DateTimeImmutable`.
	 */
	public function get_date_time(): \DateTimeImmutable {
		$today = $this->get_overridden_time_string() ?? 'now';
		return new \DateTimeImmutable( $today );
	}

	/**
	 * Return the current or overridden time as a unix timestamp.
	 */
	public function get_timestamp(): int {
		return $this->get_date_time()->getTimestamp();
	}

	/**
	 * Return the current or overridden time formatted as per
	 * `DateTimeImmutable->format()`.
	 */
	public function format( string $format ): string {
		return $this->get_date_time()->format( $format );
	}
}
