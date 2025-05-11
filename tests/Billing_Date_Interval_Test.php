<?php
// phpcs:disable Squiz.PHP.CommentedOutCode.Found
declare( strict_types = 1 );

namespace Sirbrillig;

require dirname( __DIR__ ) . '/src/Billing_Date_Interval.php';
require dirname( __DIR__ ) . '/src/Date_Interval_Parts.php';

use Sirbrillig\Billing_Date_Interval;
use Sirbrillig\Date_Interval_Parts;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class Billing_Date_Interval_Test extends TestCase {
	#[DataProvider( 'month_interval_provider' )]
	public function test_apply_interval_to_timestamp_months(
		int $interval_count,
		string $date,
		string $expected_date
	) {
		$interval_unit = 'months';
		$this->run_interval_test( $interval_count, $interval_unit, $date, $expected_date );
	}

	#[DataProvider( 'month_interval_provider' )]
	public function test_apply_interval_to_timestamp_month(
		int $interval_count,
		string $date,
		string $expected_date
	) {
		$interval_unit = 'month';
		$this->run_interval_test( $interval_count, $interval_unit, $date, $expected_date );
	}

	#[DataProvider( 'month_interval_provider' )]
	public function test_apply_interval_parts_to_timestamp_month(
		int $interval_count,
		string $date,
		string $expected_date
	) {
		$interval_unit  = 'month';
		$renew_interval = "{$interval_count} {$interval_unit}";
		$interval_parts = Billing_Date_Interval::get_interval_parts_from_renew_interval( $renew_interval );
		$this->run_interval_test_parts( $interval_parts, $date, $expected_date );
	}

	#[DataProvider( 'month_interval_original_date_provider' )]
	public function test_apply_interval_to_timestamp_month_original_date(
		int $interval_count,
		string $date,
		string $original_date,
		string $expected_date,
	) {
		$interval_unit = 'month';
		$this->run_interval_test( $interval_count, $interval_unit, $date, $expected_date, $original_date );
	}

	#[DataProvider( 'month_interval_original_date_provider' )]
	public function test_apply_interval_parts_to_timestamp_month_original_date(
		int $interval_count,
		string $date,
		string $original_date,
		string $expected_date,
	) {
		$interval_unit  = 'month';
		$renew_interval = "{$interval_count} {$interval_unit}";
		$interval_parts = Billing_Date_Interval::get_interval_parts_from_renew_interval( $renew_interval );
		$this->run_interval_test_parts( $interval_parts, $date, $expected_date, $original_date );
	}

	#[DataProvider( 'month_interval_provider' )]
	public function test_apply_interval_to_timestamp_month_constant(
		int $interval_count,
		string $date,
		string $expected_date
	) {
		$interval_unit = Billing_Date_Interval::INTERVAL_ONE_MONTH;
		$this->run_interval_test( $interval_count, $interval_unit, $date, $expected_date );
	}

	#[DataProvider( 'day_interval_provider' )]
	public function test_apply_interval_to_timestamp_day(
		int $interval_count,
		string $date,
		string $expected_date
	) {
		$interval_unit = 'days';
		$this->run_interval_test( $interval_count, $interval_unit, $date, $expected_date );
	}

	#[DataProvider( 'year_interval_provider' )]
	public function test_apply_interval_to_timestamp_year(
		int $interval_count,
		string $date,
		string $expected_date
	) {
		$interval_unit = 'years';
		$this->run_interval_test( $interval_count, $interval_unit, $date, $expected_date );
	}

	#[DataProvider( 'year_interval_original_date_provider' )]
	public function test_apply_interval_to_timestamp_year_original_date(
		int $interval_count,
		string $date,
		string $original_date,
		string $expected_date
	) {
		$interval_unit = 'years';
		$this->run_interval_test( $interval_count, $interval_unit, $date, $expected_date, $original_date );
	}

	#[DataProvider( 'year_interval_provider' )]
	public function test_apply_interval_to_timestamp_year_constant(
		int $interval_count,
		string $date,
		string $expected_date
	) {
		$interval_unit = Billing_Date_Interval::INTERVAL_ONE_YEAR;
		$this->run_interval_test( $interval_count, $interval_unit, $date, $expected_date );
	}

	#[DataProvider( 'renew_interval_provider' )]
	public function test_get_interval_parts_from_renew_interval(
		string $renew_interval,
		string $date,
		string $expected_date,
	) {
		$date_timestamp = ( new \DateTimeImmutable( $date . ' 00:00:00' ) )->getTimestamp();
		$interval_parts = Billing_Date_Interval::get_interval_parts_from_renew_interval( $renew_interval );
		$actual_time    = Billing_Date_Interval::apply_interval_to_timestamp(
			$interval_parts->interval_count,
			$interval_parts->interval_unit,
			$date_timestamp
		);
		$actual_date    = ( new \DateTimeImmutable( '@' . $actual_time ) )->format( 'Y-m-d' );
		$this->assertSame(
			$expected_date,
			$actual_date,
			"Expected moving {$renew_interval} from {$date} would be {$expected_date} but got {$actual_date}"
		);
	}

	#[DataProvider( 'intervals_between_timestamps_provider' )]
	public function test_get_intervals_between_timestamps(
		int $interval_count,
		string $interval_unit,
		int $start_time,
		int $end_time,
		float $expected_intervals
	) {
		$actual = Billing_Date_Interval::get_intervals_between_timestamps(
			$start_time,
			$end_time,
			$interval_count,
			$interval_unit,
		);
		$this->assertSame( $expected_intervals, $actual );
	}

	/************ providers and helper functions below ****************/

	private function run_interval_test(
		int $interval_count,
		string $interval_unit,
		string $date,
		string $expected_date,
		?string $original_date = null,
	) {
		$interval_parts                 = new Date_Interval_Parts();
		$interval_parts->interval_count = $interval_count;
		$interval_parts->interval_unit  = $interval_unit;
		$this->run_interval_test_parts(
			$interval_parts,
			$date,
			$expected_date,
			$original_date,
		);
	}

	private function run_interval_test_parts(
		Date_Interval_Parts $interval_parts,
		string $date,
		string $expected_date,
		?string $original_date = null,
	) {
		$interval_count     = $interval_parts->interval_count;
		$interval_unit      = $interval_parts->interval_unit;
		$date_timestamp     = ( new \DateTimeImmutable( $date . ' 00:00:00' ) )->getTimestamp();
		$original_timestamp = $original_date
			? ( new \DateTimeImmutable( $original_date . ' 00:00:00' ) )->getTimestamp()
			: null;
		$actual_time        = Billing_Date_Interval::apply_interval_to_timestamp(
			$interval_count,
			$interval_unit,
			$date_timestamp,
			$original_timestamp,
		);
		$actual_date        = ( new \DateTimeImmutable( '@' . $actual_time ) )->format( 'Y-m-d' );
		$message            = "Expected moving {$interval_count} {$interval_unit} from {$date} would be {$expected_date} but got {$actual_date}";
		if ( ! is_null( $original_date ) ) {
			$message = "Expected moving {$interval_count} {$interval_unit} from {$date} (with an origin of {$original_date}) would be {$expected_date} but got {$actual_date}";
		}
		$this->assertSame(
			$expected_date,
			$actual_date,
			$message,
		);
	}

	public static function month_interval_provider(): array {
		return [
			[
				0,
				'2020-10-01',
				'2020-10-01',
			],
			[
				1,
				'2020-10-01',
				'2020-11-01',
			],
			[
				1,
				'2020-10-31',
				'2020-11-30',
			],
			[
				1,
				'2020-12-31',
				'2021-01-31',
			],
			[
				2,
				'2020-12-31',
				'2021-02-28',
			],
			[
				3,
				'2020-11-30',
				'2021-02-28',
			],
			[
				1,
				'2024-01-29',
				// 2024 was a leap year
				'2024-02-29',
			],
			[
				1,
				'2020-02-28',
				'2020-03-28',
			],
			[
				1,
				// 2024 was a leap year
				'2024-02-29',
				'2024-03-29',
			],
			[
				3,
				// 2024 was a leap year
				'2024-02-29',
				'2024-05-29',
			],
			[
				-1,
				'2020-02-28',
				'2020-01-28',
			],
			[
				-2,
				'2020-02-28',
				'2019-12-28',
			],
			[
				-1,
				'2020-12-31',
				'2020-11-30',
			],
			[
				-2,
				'2020-12-31',
				'2020-10-31',
			],
			[
				-3,
				'2020-12-31',
				'2020-09-30',
			],
		];
	}

	public static function month_interval_original_date_provider(): array {
		return [
			[
				0,
				'2020-10-01',
				'2020-10-01',
				'2020-10-01',
			],
			[
				1,
				'2020-10-01',
				'2020-10-01',
				'2020-11-01',
			],
			[
				1,
				'2025-01-25',
				'2024-09-30',
				'2025-02-25',
			],
			[
				1,
				'2020-10-31',
				'2020-10-31',
				'2020-11-30',
			],
			[
				1,
				'2020-02-28',
				'2020-02-28',
				'2020-03-28',
			],
			[
				1,
				'2020-02-29',
				'2020-01-31',
				'2020-03-31',
			],
			[
				0,
				'2020-02-28',
				'2020-01-31',
				'2020-02-28',
			],
			[
				-1,
				'2020-02-28',
				'2020-02-28',
				'2020-01-28',
			],
			[
				-1,
				'2020-02-28',
				'2020-03-31',
				'2020-01-31',
			],
			[
				-1,
				'2020-02-29',
				'2020-03-31',
				'2020-01-31',
			],
			[
				3,
				'2020-11-30',
				'2020-08-30',
				'2021-02-28',
			],
		];
	}

	public static function day_interval_provider(): array {
		return [
			[
				0,
				'2022-01-01',
				'2022-01-01',
			],
			[
				10,
				'2022-01-01',
				'2022-01-11',
			],
			[
				1,
				'2021-02-28',
				'2021-03-01',
			],
			[
				2,
				// 2024 was a leap year
				'2024-02-27',
				'2024-02-29',
			],
			[
				30,
				'2022-01-01',
				'2022-01-31',
			],
			[
				30,
				'2022-01-02',
				'2022-02-01',
			],
			[
				30,
				'2022-12-10',
				'2023-01-09',
			],
			[
				-10,
				'2022-12-11',
				'2022-12-01',
			],
			[
				-10,
				'2022-12-10',
				'2022-11-30',
			],
			[
				-1,
				'2022-01-02',
				'2022-01-01',
			],
			[
				-2,
				'2022-01-02',
				'2021-12-31',
			],
			[
				-2,
				'2022-03-02',
				'2022-02-28',
			],
		];
	}

	public static function year_interval_provider(): array {
		return [
			[
				0,
				'2023-03-30',
				'2023-03-30',
			],
			[
				1,
				'2023-03-30',
				'2024-03-30',
			],
			[
				2,
				'2023-03-30',
				'2025-03-30',
			],
			[
				2,
				'2023-02-28',
				'2025-02-28',
			],
			[
				1,
				// 2024 was a leap year
				'2024-02-29',
				'2025-02-28',
			],
			[
				2,
				// 2024 was a leap year
				'2024-02-29',
				'2026-02-28',
			],
			[
				3,
				// 2024 was a leap year
				'2024-02-29',
				'2027-02-28',
			],
			[
				6,
				// 2024 was a leap year
				'2024-02-29',
				'2030-02-28',
			],
			[
				4,
				// 2024 was a leap year
				'2024-02-29',
				// 2028 is a leap year
				'2028-02-29',
			],
			[
				-2,
				// 2024 was a leap year
				'2024-02-29',
				'2022-02-28',
			],
			[
				-2,
				'2023-02-28',
				'2021-02-28',
			],
			[
				-4,
				// 2028 is a leap year
				'2028-02-29',
				// 2024 was a leap year
				'2024-02-29',
			],
			[
				-2,
				'2026-03-01',
				// 2024 was a leap year
				'2024-03-01',
			],
			[
				-6,
				// 2024 was a leap year
				'2024-02-29',
				'2018-02-28',
			],
			[
				-8,
				// 2024 was a leap year
				'2024-02-29',
				// 2016 was a leap year
				'2016-02-29',
			],
			[
				-9,
				// 2024 was a leap year
				'2024-02-29',
				'2015-02-28',
			],
		];
	}

	public static function year_interval_original_date_provider(): array {
		return [
			[
				1,
				'2023-03-30',
				'2023-03-30',
				'2024-03-30',
			],
			[
				1,
				// 2024 was a leap year
				'2024-02-29',
				'2024-02-29',
				'2025-02-28',
			],
			[
				0,
				'2023-02-28',
				// 2020 was a leap year
				'2020-02-29',
				'2023-02-28',
			],
			[
				1,
				'2023-02-28',
				// 2020 was a leap year
				'2020-02-29',
				// 2024 was a leap year, but still we want the 28th because most domain
				// registrars operate like that.
				'2024-02-28',
			],
			[
				1,
				'2025-03-26',
				'2024-09-11',
				'2026-03-26',
			],
			[
				-8,
				// 2024 was a leap year
				'2024-02-28',
				'2024-02-28',
				// 2016 was a leap year
				'2016-02-28',
			],
			[
				-8,
				// 2024 was a leap year
				'2024-02-28',
				'2024-02-29',
				// 2016 was a leap year, but still we want the 28th because most domain
				// registrars operate like that.
				'2016-02-28',
			],
		];
	}

	public static function renew_interval_provider(): array {
		return [
			[
				'0 day',
				'2024-01-31',
				'2024-01-31',
			],
			[
				'1 year',
				'2024-01-31',
				'2025-01-31',
			],
			[
				'1 month',
				'2024-01-31',
				'2024-02-29',
			],
			[
				'1 day',
				'2024-01-31',
				'2024-02-01',
			],
			[
				'+1 day',
				'2024-01-31',
				'2024-02-01',
			],
			[
				'-1 day',
				'2024-01-31',
				'2024-01-30',
			],
		];
	}

	public static function intervals_between_timestamps_provider() {
		return [
			// Test basic cases (intervals that are a whole number of the
			// billing period).
			[ 1, 'year', strtotime( '2025-01-01' ), strtotime( '2026-01-01' ), 1 ],
			[ 1, 'year', strtotime( '2025-01-01' ), strtotime( '2027-01-01' ), 2 ],
			[ 1, 'year', strtotime( '2025-01-01' ), strtotime( '2030-01-01' ), 5 ],
			[ 1, 'month', strtotime( '2025-01-01' ), strtotime( '2025-02-01' ), 1 ],
			[ 1, 'month', strtotime( '2025-01-01' ), strtotime( '2025-03-01' ), 2 ],
			[ 1, 'month', strtotime( '2025-01-01' ), strtotime( '2026-01-01' ), 12 ],
			[ 1, 'month', strtotime( '2025-01-01' ), strtotime( '2030-01-01' ), 60 ],
			// Test intervals that are approximately half of an annual billing
			// period. Note that there are 181 days between January 1 and July
			// 1, but 182 in a leap year, whereas there are always 184 days
			// between July 1 and January 1.
			[ 1, 'year', strtotime( '2025-01-01' ), strtotime( '2025-07-01' ), 181 / 365 ],
			[ 1, 'year', strtotime( '2028-01-01' ), strtotime( '2028-07-01' ), 182 / 366 ],
			[ 1, 'year', strtotime( '2026-07-01' ), strtotime( '2027-01-01' ), 184 / 365 ],
			[ 1, 'year', strtotime( '2027-07-01' ), strtotime( '2028-01-01' ), 184 / 366 ],
			// Test intervals that are approximately one and a half annual
			// billing periods. In this case, the leap year only affects the
			// calculation if the second (fractional) billing period crosses
			// February 29.
			[ 1, 'year', strtotime( '2026-01-01' ), strtotime( '2027-07-01' ), 1 + 181 / 365 ],
			[ 1, 'year', strtotime( '2027-01-01' ), strtotime( '2028-07-01' ), 1 + 182 / 366 ],
			[ 1, 'year', strtotime( '2028-01-01' ), strtotime( '2029-07-01' ), 1 + 181 / 365 ],
			// Test intervals that are approximately half of a monthly billing
			// period, for months of different lengths.
			[ 1, 'month', strtotime( '2027-01-01' ), strtotime( '2027-01-16' ), 15 / 31 ],
			[ 1, 'month', strtotime( '2027-02-01' ), strtotime( '2027-02-16' ), 15 / 28 ],
			[ 1, 'month', strtotime( '2028-02-01' ), strtotime( '2028-02-16' ), 15 / 29 ],
			[ 1, 'month', strtotime( '2027-02-15' ), strtotime( '2027-03-02' ), 15 / 28 ],
			[ 1, 'month', strtotime( '2028-02-15' ), strtotime( '2028-03-02' ), 16 / 29 ],
			[ 1, 'month', strtotime( '2027-04-01' ), strtotime( '2027-04-16' ), 15 / 30 ],
			// Test intervals that cross one or more month boundaries, for
			// months of different lengths (e.g. part of a 30 day month, a 30
			// day month plus part of a 31 day month, and a 30 day month and 31
			// day month plus part of another 30 day month).
			[ 1, 'month', strtotime( '2024-04-24' ), strtotime( '2024-05-01' ), 7 / 30 ],
			[ 1, 'month', strtotime( '2024-04-24' ), strtotime( '2024-06-01' ), 1 + 8 / 31 ],
			[ 1, 'month', strtotime( '2024-04-24' ), strtotime( '2024-07-01' ), 2 + 7 / 30 ],
			// Test intervals that start at the end of the month, for monthly
			// billing periods. This depends on the specific rules the billing
			// codebase actually uses for incrementing monthly subscriptions.
			[ 1, 'month', strtotime( '2027-01-31' ), strtotime( '2027-02-28' ), 1 ],
			[ 1, 'month', strtotime( '2028-01-31' ), strtotime( '2028-02-28' ), 28 / 29 ],
			[ 1, 'month', strtotime( '2028-01-31' ), strtotime( '2028-02-29' ), 1 ],
			[ 1, 'month', strtotime( '2028-01-31' ), strtotime( '2028-03-28' ), 1 + 28 / 29 ],
			[ 1, 'month', strtotime( '2028-01-31' ), strtotime( '2028-03-29' ), 2 ],
			[ 1, 'month', strtotime( '2028-01-31' ), strtotime( '2028-03-31' ), 2 + 2 / 31 ],
			// Test some misc intervals.
			[ 1, 'day', strtotime( '2024-01-20' ), strtotime( '2024-07-01' ), 163 ],
			[ 2, 'day', strtotime( '2024-01-20' ), strtotime( '2024-07-01' ), 163 / 2 ],
			[ 1, 'month', strtotime( '2024-01-01' ), strtotime( '2024-07-01' ), 6 ],
			[ 2, 'month', strtotime( '2024-01-01' ), strtotime( '2024-07-01' ), 3 ],
		];
	}
}
