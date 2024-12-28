<?php

namespace AdventOfCode\Year2021;

/**
 * Day 07: The Treachery of Whales
 */
class Day07 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var integer
	 */
	private int $part;

	/**
	 * Whether to use the test data.
	 *
	 * @var bool
	 */
	private bool $is_test;

	/**
	 * Parsed data from the input file.
	 *
	 * @var array
	 */
	private array $data;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return integer
	 */
	public function run(): int {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Determine the horizontal position that the crabs can align to using the least fuel possible.
	 *         How much fuel must they spend to align to that position?
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		// Get the median value of the data
		sort( $this->data );
		$middle = (int) floor( count( $this->data ) / 2 );
		$median = $this->data[ $middle ];

		// Calculate the total fuel required to move all crabs to the median position
		$fuel = 0;
		foreach ( $this->data as $position ) {
			$fuel += abs( $position - $median );
		}

		return $fuel;
	}

	/**
	 * Part 2: As each crab moves, moving further becomes more expensive.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		// Get the mean value of the data
		sort( $this->data );
		$mean = array_sum( $this->data ) / count( $this->data );
		$floor = (int) floor( $mean ); // Round down
		$ceil = (int) ceil( $mean ); // Round up

		// Calculate the total fuel required to move all crabs to the mean position
		// Try both the rounded down and rounded up values and return the lower of the two.
		$fuel_floor = $this->calculate_fuel_consumption( $floor );
		$fuel_ceil = $this->calculate_fuel_consumption( $ceil );

		// Return the lower of the two values
		return min( $fuel_floor, $fuel_ceil );
	}

	/**
	 * Calculates the total fuel required to move all crabs to the mean position.
	 *
	 * @param int $rounded_mean The rounded mean value.
	 *
	 * @return int
	 */
	private function calculate_fuel_consumption( int $rounded_mean ): int {
		return array_sum( array_map( function ( $value ) use ( $rounded_mean ) {
			$distance = abs( $value - $rounded_mean );

			return $distance * ( $distance + 1 ) / 2;
		}, $this->data ) );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-07-test.txt' : '/data/day-07.txt';

		return explode( ",", trim( file_get_contents( __DIR__ . $file ) ) );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 * @param bool    $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day07  = new Day07( $test, $part );
	$result = $day07->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 37,
			'real' => 345197,
		],
		2 => [
			'test' => 168,
			'real' => 96361606,
		],
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $test ? $expected_values[ $part ]['test'] : $expected_values[ $part ]['real'] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for part and test mode
while ( true ) {
	$part = (int) trim( readline( 'Which part do you want to run? (1/2): ' ) );
	if ( ! in_array( $part, [ 1, 2 ], true ) ) {
		echo 'Invalid part. Please enter 1 or 2.' . PHP_EOL;
		continue;
	}

	while ( true ) {
		$test = strtolower( trim( readline( 'Do you want to run the test? (y/n): ' ) ) );
		if ( in_array( $test, [ 'y', 'n' ], true ) ) {
			$test_mode = $test === 'y';
			run_part( $part, $test_mode );
			break 2;
		}
		echo 'Invalid input. Please enter y or n.' . PHP_EOL;
	}
}
