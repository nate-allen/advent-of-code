<?php

/**
 * Day 18: Lavaduct Lagoon
 */
class Day18 {
	/**
	 * The puzzle data.
	 *
	 * @var array
	 */
	private array $data;

	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var int
	 */
	private int $part;

	/**
	 * Movement directions.
	 *
	 * @var array
	 */
	private array $directions = [
		'R' => [ 1, 0 ],   // Move right: Increase x by 1, no change in y
		'D' => [ 0, 1 ],   // Move down: No change in x, increase y by 1
		'L' => [ - 1, 0 ], // Move left: Decrease x by 1, no change in y
		'U' => [ 0, - 1 ], // Move up: No change in x, decrease y by 1
	];

	public function __construct(string $test, int $part ) {
		$this->parse_data($test);
		$this->part = $part;

		if ( $part === 2 ) {
			$this->directions = array_values( $this->directions );
		}
	}

	/**
	 * Part 1: Follow the dig plan. How many cubic meters of lava could it hold?
	 *
	 * @return int Cubic meters
	 */
	public function part_1(): int {
		return $this->get_cubic_meters();
	}

	/**
	 * Part 2: Follow the dig plan. How many cubic meters of lava could it hold?
	 *
	 * @return int Cubic meters
	 */
	public function part_2(): int {
		return $this->get_cubic_meters();
	}

	/**
	 * Returns the cubic meters of lava the crucible can hold.
	 *
	 * @return float|int
	 */
	private function get_cubic_meters(): float|int {
		$current_position   = [ 0, 0 ];
		$polygon_points     = [ $current_position ];
		$perimeter_distance = 0;

		foreach ( $this->data as $line ) {
			[ $distance, $direction ] = $this->get_distance_and_direction( $line );

			// Update the current position based on the direction and distance
			$current_position = [
				$current_position[0] + $direction[1] * $distance,
				$current_position[1] + $direction[0] * $distance
			];

			// Add the updated position to the list of polygon points
			$polygon_points[] = $current_position;
			// Increase the perimeter distance
			$perimeter_distance += $distance;
		}

		$polygon_area         = $this->get_polygon_area( $polygon_points );
		$perimeter_adjustment = $perimeter_distance / 2 + 1; // Add 1 to account for the corners.

		return $polygon_area + $perimeter_adjustment;
	}

	/**
	 * Parses a string and returns an array with the distance and direction.
	 *
	 * @param $string string The string to parse.
	 *
	 * @return array
	 */
	private function get_distance_and_direction( string $string ): array {
		$array = [];
		$parts = explode( ' ', $string );

		if ( 1 === $this->part ) {
			$array[0] = intval( $parts[1] );
			$array[1] = $this->directions[ $parts[0] ];
		} else {
			$parts[2] = str_replace( [ '(', '#', ')' ], '', $parts[2] );

			$array[0] = hexdec( substr( $parts[2], 0, 5 ) );
			$array[1] = $this->directions[ substr( $parts[2], 5, 1 ) ];
		}

		return $array;
	}

	/**
	 * Returns the area of a polygon.
	 *
	 * @param $points
	 *
	 * @return float|int
	 */
	private function get_polygon_area( $points ): float|int {
		$area = 0;
		$n    = count( $points );

		for ( $i = 0; $i < $n - 1; $i ++ ) {
			$area += ( $points[ $i ][0] * $points[ $i + 1 ][1] ) - ( $points[ $i + 1 ][0] * $points[ $i ][1] );
		}

		// Closing the polygon by taking the last and first points
		$area += ( $points[ $n - 1 ][0] * $points[0][1] ) - ( $points[0][0] * $points[ $n - 1 ][1] );

		// Absolute value and divided by 2
		return abs( $area ) / 2;
	}

	private function parse_data(string $test): void {
		$file = $test ? '/data/day-18-test.txt' : '/data/day-18.txt';
		$this->data = explode("\n", trim(file_get_contents(__DIR__ . $file)));
	}
}

// Prompt which part to run and if it should use the test data.
while ( true ) {
	$part = trim( readline( 'Which part do you want to run? (1/2)' ) );
	if ( function_exists( "part_$part" ) ) {
		while ( true ) {
			$test = trim( strtolower( readline( 'Do you want to run the test? (y/n)' ) ) );
			if ( in_array( $test, array( 'y', 'n' ), true ) ) {
				$test = 'y' === $test;
				call_user_func( "part_$part", $test );
				break;
			}
			echo 'Please enter y or n' . PHP_EOL;
		}
		break;
	}
	echo 'Please enter 1 or 2' . PHP_EOL;
}

function part_1( $test = false ) {
	$start    = microtime( true );
	$day18    = new Day18( $test, 1 );
	$result   = $day18->part_1();
	$end      = microtime( true );
	$expected = $test ? 62 : 35991;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day18    = new Day18( $test, 2 );
	$result   = $day18->part_2();
	$end      = microtime( true );
	$expected = $test ? 952408144115 : 54_058_824_661_845;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
