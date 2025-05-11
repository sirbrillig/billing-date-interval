<?php declare( strict_types=1 );

namespace Sirbrillig;

final class Billing_Date_Interval {
	const INTERVAL_ONE_DAY   = 'day';
	const INTERVAL_ONE_MONTH = 'month';
	const INTERVAL_ONE_YEAR  = 'year';

	/**
	 * Add or subtract an interval to a date.
	 *
	 * Adds an interval to a date, while maintaining consistent logic for
	 * nonexistent dates (like Feb 30th). Will attempt to maintain day of month
	 * from original date (like March 30th, for a subscription originated on
	 * January 30th).
	 *
	 * Can also be used to subtract a date interval if the `interval_count` is
	 * negative.
	 *
	 * @param int $interval_count Number of units to add.
	 * @param string $interval_unit Type of interval - 'day', 'month', 'year'
	 * @param int $starting_timestamp Timestamp you're adding to.
	 * @param int|null $original_timestamp If this is adding eg: a month to a subscription, we need to know the original date.
	 *
	 * @return int Adjusted timestamp.
	 * @throws Exception
	 */
	public static function apply_interval_to_timestamp(
		int $interval_count,
		string $interval_unit,
		int $starting_timestamp,
		?int $original_timestamp = null,
	): int {
		if ( 0 === $interval_count ) {
			return $starting_timestamp;
		}

		$interval_unit = match ( $interval_unit ) {
			self::INTERVAL_ONE_DAY, 'days' => self::INTERVAL_ONE_DAY,
			self::INTERVAL_ONE_MONTH, 'months' => self::INTERVAL_ONE_MONTH,
			self::INTERVAL_ONE_YEAR, 'years' => self::INTERVAL_ONE_YEAR,
			default => throw new \Exception( sprintf( 'Unknown bill period: %s', $interval_unit ) ),
		};

		$date                   = new \DateTimeImmutable();
		$date                   = $date->setTimestamp( $starting_timestamp );
		$is_start_date_leap_day = $date->format( 'm-d' ) === '02-29';

		// Use the original day of the month if the day of the month is likely to
		// be near the end of the month.
		$is_original_timestamp_end_of_month = $original_timestamp ? intval( gmdate( 'j', $original_timestamp ) ) > 28 : false;
		$is_start_date_end_of_month         = intval( gmdate( 'j', $starting_timestamp ) ) >= 28;
		$original_day_in_month              = $is_original_timestamp_end_of_month && $is_start_date_end_of_month
			? intval( gmdate( 'j', $original_timestamp ) )
			: null;

		// Days are simple
		if ( 'day' === $interval_unit && $interval_count > 0 ) {
			return $date->modify( '+' . abs( $interval_count ) . ' day' )->getTimestamp();
		}
		if ( 'day' === $interval_unit && $interval_count < 0 ) {
			return $date->modify( '-' . abs( $interval_count ) . ' day' )->getTimestamp();
		}

		// Years are mostly simple, but we have to account for leap years which
		// have a Febrary 29.
		if ( self::INTERVAL_ONE_YEAR === $interval_unit && $interval_count > 0 ) {
			$day_in_month = (int) $date->format( 'j' );

			$new_date             = $date->modify( '+' . abs( $interval_count ) . ' year' );
			$is_new_date_in_march = $new_date->format( 'm-d' ) === '03-01';
			if ( $is_start_date_leap_day && $is_new_date_in_march ) {
				$new_date = $new_date->modify( '-1 days' );
			}

			$new_date              = $new_date->setTimestamp( strtotime( $new_date->format( 'Y-m-01' ) ) );
			$total_days_in_month   = (int) $new_date->format( 't' );
			$days_to_move_in_month = min( $day_in_month, $total_days_in_month );
			return $new_date->modify( '+' . $days_to_move_in_month - 1 . ' days' )->getTimestamp();
		}
		if ( self::INTERVAL_ONE_YEAR === $interval_unit && $interval_count < 0 ) {
			$day_in_month = (int) $date->format( 'j' );

			$new_date             = $date->modify( '-' . abs( $interval_count ) . ' year' );
			$is_new_date_leap_day = $new_date->format( 'm-d' ) === '02-29';
			if ( $is_start_date_leap_day && ! $is_new_date_leap_day ) {
				$new_date = $new_date->modify( '-1 days' );
			}

			$new_date              = $new_date->setTimestamp( strtotime( $new_date->format( 'Y-m-01' ) ) );
			$total_days_in_month   = (int) $new_date->format( 't' );
			$days_to_move_in_month = min( $day_in_month, $total_days_in_month );
			return $new_date->modify( '+' . $days_to_move_in_month - 1 . ' days' )->getTimestamp();
		}

		// Months need to never go past the month boundary. PHP treats "1 month" as
		// a number of days. To PHP, January 31 plus "1 month" is March 2, but we
		// want it to be Febrary 28. To PHP, December 31 minus "1 month" is
		// December 1, but we want it to be November 30.
		if ( self::INTERVAL_ONE_MONTH === $interval_unit && $interval_count > 0 ) {
			$day_in_month = (int) $date->format( 'j' );
			if ( is_int( $original_day_in_month ) ) {
				$day_in_month = $original_day_in_month;
			}
			$date                  = $date->modify( 'first day of +' . $interval_count . ' months' );
			$total_days_in_month   = (int) $date->format( 't' );
			$days_to_move_in_month = min( $day_in_month, $total_days_in_month );
			return $date->modify( '+' . $days_to_move_in_month - 1 . ' days' )->getTimestamp();
		}
		if ( self::INTERVAL_ONE_MONTH === $interval_unit && $interval_count < 0 ) {
			$day_in_month = (int) $date->format( 'j' );
			if ( is_int( $original_day_in_month ) ) {
				$day_in_month = $original_day_in_month;
			}
			$date                  = $date->modify( 'first day of -' . abs( $interval_count ) . ' months' );
			$total_days_in_month   = (int) $date->format( 't' );
			$days_to_move_in_month = min( $day_in_month, $total_days_in_month );
			return $date->modify( '+' . $days_to_move_in_month - 1 . ' days' )->getTimestamp();
		}

		throw new \Exception( sprintf( 'Failed to adjust timestamp by %d %s', $interval_count, $interval_unit ) );
	}

	/**
	 * Convert a full interval string (eg: `1 year`) to data suitable for
	 * `apply_interval_to_timestamp()` or `apply_interval_parts_to_timestamp()`.
	 *
	 * Can also handle intervals with + or - signs (eg: `-3 months`).
	 */
	public static function get_interval_parts_from_renew_interval( string $renew_interval ): Date_Interval_Parts {
		$matches = [];
		preg_match( '/^([+-])?(\d+) (\w+)$/', $renew_interval, $matches );
		if ( count( $matches ) !== 3 && count( $matches ) !== 4 ) {
			throw new \Exception( sprintf( 'Could not parse renew_interval "%s"', $renew_interval ) );
		}
		$has_sign              = count( $matches ) === 4;
		$sign_part             = $has_sign ? $matches[1] : '+';
		$count_part            = $has_sign ? $matches[2] : $matches[1];
		$unit_part             = $has_sign ? $matches[3] : $matches[2];
		$parts                 = new Date_Interval_Parts();
		$parts->interval_count = (int) $count_part;
		if ( '-' === $sign_part ) {
			$parts->interval_count = $parts->interval_count * -1;
		}
		$parts->interval_unit = $unit_part;
		return $parts;
	}

	/**
	 * Add or subtract an interval to a date.
	 *
	 * Just like `apply_interval_to_timestamp()` but uses a `Date_Interval_Parts`
	 * parameter which can be generated by
	 * `get_interval_parts_from_renew_interval()`. Sometimes this is more
	 * convenient although using `apply_interval_to_timestamp()` directly is
	 * recommended.
	 *
	 * @param Date_Interval_Parts $interval_parts The interval to use.
	 * @param int $starting_timestamp Timestamp you're adding to.
	 * @param int|null $original_timestamp If this is adding eg: a month to a subscription, we need to know the original date.
	 *
	 * @return int Adjusted timestamp.
	 * @throws Exception
	 */
	public static function apply_interval_parts_to_timestamp(
		Date_Interval_Parts $interval_parts,
		int $starting_timestamp,
		?int $original_timestamp = null,
	): int {
		return self::apply_interval_to_timestamp(
			$interval_parts->interval_count,
			$interval_parts->interval_unit,
			$starting_timestamp,
			$original_timestamp,
		);
	}

	/**
	 * Return the number of (billing) intervals between two timestamps.
	 *
	 * For example, if the interval is "1 year" and there are 2.5 years between
	 * the two timestamps, this will return 2.5. If the interval is "1 month"
	 * instead, this will return 18 (the number of months in 2.5 years).
	 *
	 * If you need to transform a bill period string into interval data for this
	 * function (eg: "1 year" into 1 and 'year'), use
	 * `get_interval_parts_from_renew_interval()`.
	 *
	 * @param int $start_time The first UNIX timestamp.
	 * @param int $end_time The second UNIX timestamp.
	 * @param int $interval_count Number of units to add.
	 * @param string $interval_unit Type of interval.
	 *
	 * @return int Adjusted timestamp.
	 */
	public static function get_intervals_between_timestamps(
		int $start_time,
		int $end_time,
		int $interval_count,
		string $interval_unit,
	): float {
		$intervals_between = 0.0;
		// Throw away time and just use day since a day is our smallest unit.
		$start_time = strtotime( gmdate( 'Y-m-d', $start_time ) );
		$end_time   = strtotime( gmdate( 'Y-m-d', $end_time ) );
		$temp_time  = $start_time;
		do {
			$new_time = self::apply_interval_to_timestamp(
				$interval_count,
				$interval_unit,
				$temp_time,
			);
			if ( $new_time > $end_time ) {
				$intervals_between += max( 0, ( $end_time - $temp_time ) / ( $new_time - $temp_time ) );
				break;
			}
			$temp_time = $new_time;
			++$intervals_between;
		} while ( true );
		return $intervals_between;
	}
}
