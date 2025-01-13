<?php

namespace AdventOfCode\Year2020;

/**
 * Day 05: Binary Boarding
 */
class Day05 {
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
	 * Part 1: Look through all the boarding passes and find the highest seat ID.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$highest_seat_id = 0;

		foreach ( $this->data as $boarding_pass ) {
			$boarding_pass = str_replace( [ 'F', 'B', 'L', 'R' ], [ '0', '1', '0', '1' ], $boarding_pass );
			$seat_id       = bindec( $boarding_pass );

			$highest_seat_id = max( $highest_seat_id, $seat_id );
		}

		return $highest_seat_id;
	}

	/**
	 * Part 2: Find the missing seat ID that is not at the front or back of the plane.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$seat_ids = [];

		foreach ( $this->data as $boarding_pass ) {
			$boarding_pass = str_replace( [ 'F', 'B', 'L', 'R' ], [ '0', '1', '0', '1' ], $boarding_pass );
			$seat_id       = bindec( $boarding_pass );

			$seat_ids[] = $seat_id;
		}

		sort( $seat_ids );

		$missing_seat_id = 0;
		$last_seat_id    = $seat_ids[0];

		foreach ( $seat_ids as $seat_id ) {
			if ( $seat_id - $last_seat_id > 1 ) {
				$missing_seat_id = $last_seat_id + 1;
				break;
			}

			$last_seat_id = $seat_id;
		}

		return $missing_seat_id;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-05-test.txt' : '/data/day-05.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return $lines;
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
	$day05  = new Day05( $test, $part );
	$result = $day05->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 820,
			'real' => 866,
		],
		2 => [
			'test' => 120,
			'real' => 583,
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
