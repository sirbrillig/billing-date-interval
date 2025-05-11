<?php
// phpcs:disable Squiz.PHP.CommentedOutCode.Found
declare( strict_types = 1 );

namespace Sirbrillig;

require dirname( __DIR__ ) . '/src/Store_Time.php';

use Sirbrillig\Store_Time;
use PHPUnit\Framework\TestCase;

class Store_Time_Test extends TestCase {
	public function tearDown(): void {
		Store_Time::$is_testing_environment = false;
		Store_Time::clear_override();
		parent::tearDown();
	}

	public function test_store_time_has_override_returns_false_without_override() {
		$actual = ( new Store_Time() )->has_override();
		$this->assertFalse( $actual );
	}

	public function test_store_time_has_override_returns_false_with_override_outside_test() {
		Store_Time::$is_testing_environment = false;
		$mock_time                          = '2020-01-01 00:00:00';
		Store_Time::set_override( $mock_time );
		$actual = ( new Store_Time() )->has_override();
		$this->assertFalse( $actual );
	}

	public function test_store_time_has_override_returns_true_with_override() {
		Store_Time::$is_testing_environment = true;
		$mock_time                          = '2020-01-01 00:00:00';
		Store_Time::set_override( $mock_time );
		$actual = ( new Store_Time() )->has_override();
		$this->assertTrue( $actual );
	}

	public function test_store_time_get_timestamp_returns_timestamp() {
		$expected = time();
		$actual   = ( new Store_Time() )->get_timestamp();
		$this->assertEqualsWithDelta( $expected, $actual, 400 );
	}

	public function test_store_time_get_timestamp_with_override_returns_override() {
		Store_Time::$is_testing_environment = true;
		$mock_time                          = '2020-01-01 00:00:00';
		Store_Time::set_override( $mock_time );
		$expected = strtotime( $mock_time );
		$actual   = ( new Store_Time() )->get_timestamp();
		$this->assertEquals( $expected, $actual );
	}

	public function test_store_time_get_timestamp_with_override_but_outside_test_returns_actual_timestamp() {
		Store_Time::$is_testing_environment = false;
		$mock_time                          = '2020-01-01 00:00:00';
		Store_Time::set_override( $mock_time );
		$expected = time();
		$actual   = ( new Store_Time() )->get_timestamp();
		$this->assertEqualsWithDelta( $expected, $actual, 400 );
	}
}
