<?php

namespace AdventOfCode\Year2025;

/**
 * Day 07: Laboratories
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
	 * Part 1: Count how many times the beam is split
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		[ $splits, $timelines ] = $this->simulate_beams();
		return $splits;
	}

	/**
	 * Part 2: Count the total number of quantum timelines
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		[ $splits, $timelines ] = $this->simulate_beams();
		return $timelines;
	}

	/**
	 * Simulates the beam traveling through the manifold.
	 *
	 * @return array Returns [split_count, total_timelines]
	 */
	private function simulate_beams(): array {
		// Find starting position
		$start_col = strpos( $this->data[0], 'S' );

		$beams  = [ $start_col => 1 ];
		$splits = 0;

		// Process each row
		foreach ( $this->data as $row ) {
			$new_beams = [];
			foreach ( $beams as $col => $count ) {
				if ( $row[ $col ] === '^' ) {
					// Split into both timelines
					$splits++;
					$new_beams[ $col - 1 ] = ($new_beams[ $col - 1 ] ?? 0) + $count;
					$new_beams[ $col + 1 ] = ($new_beams[ $col + 1 ] ?? 0) + $count;
				} else {
					// Continue downward
					$new_beams[ $col ] = ($new_beams[ $col ] ?? 0) + $count;
				}
			}

			$beams = $new_beams;
		}

		// Return the split count and total timelines
		return [ $splits, array_sum( $beams ) ];
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
	$day07  = new Day07( $test, $part );
	$result = $day07->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 21,
			'real' => 1539,
		],
		2 => [
			'test' => 40,
			'real' => 6479180385864,
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
