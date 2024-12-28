<?php

namespace AdventOfCode\Year2021;

/**
 * Day 06: Lanternfish
 */
class Day06 {
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
	 * Part 1: Simulate lanternfish for 80 days and return the sum of all fish.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return (int) array_sum( $this->simulate_lantern_fish( 80 ) );
	}

	/**
	 * Part 2: Simulate lanternfish for 256 days and return the sum of all fish.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return (int) array_sum( $this->simulate_lantern_fish( 256 ) );
	}

	/**
	 * Simulates the lanternfish for the specified number of days.
	 *
	 * @param integer $days The number of days to simulate.
	 *
	 * @return array
	 */
	private function simulate_lantern_fish( int $days = 0 ) {
		// Create an array of 9 empty values that represent the lanternfish countdown states.
		$lanternfish_states = array_fill( 0, 9, 0 );

		// Loop through the data and organize the fish by their state (countdown number).
		foreach ( $this->data as $fish ) {
			$lanternfish_states[ $fish ] ++;
		}

		// Loop through however many days are specified.
		for ( $day = 0; $day < $days; $day ++ ) {
			// Create an array of 9 empty values for the next day.
			$next_day_states = array_fill( 0, 9, 0 );

			// Fish that hit 0 become a 6 and the same amount are also added to 8.
			$next_day_states[6] = $lanternfish_states[0];
			$next_day_states[8] = $lanternfish_states[0];

			// All the other fish move down a spot.
			for ( $state = 1; $state < 9; $state ++ ) {
				$next_day_states[ $state - 1 ] += $lanternfish_states[ $state ];
			}

			// Copy the next day fish to the current fish to prepare for the next day.
			$lanternfish_states = $next_day_states;
		}

		// Return the lanternfish.
		return $lanternfish_states;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-06-test.txt' : '/data/day-06.txt';

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
	$day06  = new Day06( $test, $part );
	$result = $day06->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 5934,
			'real' => 380243,
		],
		2 => [
			'test' => 26984457539,
			'real' => 1708791884591,
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
