<?php

namespace AdventOfCode\Year2024;

/**
 * Day 11: Plutonian Pebbles
 */
class Day11 {
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
	 * Part 1: How many stones will you have after blinking 25 times?
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$total_stones = 0;
		foreach ( $this->data as $stone ) {
			$total_stones += $this->count_stones( $stone, 25 );
		}

		return $total_stones;
	}


	/**
	 * Part 2: How many stones will you have after blinking 75 times?
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$total_stones = 0;
		foreach ( $this->data as $stone ) {
			$total_stones += $this->count_stones( $stone, 75 );
		}

		return $total_stones;
	}

	/**
	 * Recursive method to count the total stones after a specific number of blinks.
	 *
	 * Uses a cache to store results for each stone and remaining blinks.
	 *
	 * @param integer $stone            The current stone.
	 * @param integer $remaining_blinks The remaining number of blinks.
	 * @param array   $cache            A cache of results.
	 *
	 * @return int
	 */
	private function count_stones( int $stone, int $remaining_blinks, array &$cache = [] ): int {
		if ( $remaining_blinks === 0 ) {
			return 1;
		}

		// Check the cache first.
		$cache_key = $stone . ',' . $remaining_blinks;
		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		if ( $stone === 0 ) {
			$result = $this->count_stones( 1, $remaining_blinks - 1, $cache );
		} elseif ( strlen( $stone ) % 2 === 0 ) {
			$left   = intval( substr( $stone, 0, strlen( $stone ) / 2 ) );
			$right  = intval( substr( $stone, strlen( $stone ) / 2 ) );
			$result = $this->count_stones( $left, $remaining_blinks - 1, $cache ) +
					  $this->count_stones( $right, $remaining_blinks - 1, $cache );
		} else {
			$result    = $this->count_stones( $stone * 2024, $remaining_blinks - 1, $cache );
		}

		// Cache result and return it.
		return $cache[ $cache_key ] = $result;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-11-test.txt' : '/data/day-11.txt';

		return array_map( 'intval', explode( ' ', file_get_contents( __DIR__ . $file ) ) );
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
	$day11  = new Day11( $test, $part );
	$result = $day11->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 55312,
			'real' => 197357,
		],
		2 => [
			'test' => 65601038650482,
			'real' => 234568186890978,
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
