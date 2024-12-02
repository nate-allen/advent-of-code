<?php

namespace AdventOfCode\Year2024;

/**
 * Day 02: Reactor Safety Reports
 */
class Day02 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var int
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
	 * @return int
	 */
	public function run(): int {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Determine the number of safe reports.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$total = 0;

		foreach ( $this->data as $levels ) {
			if ( $this->is_safe( $levels ) ) {
				$total ++;
			}
		}

		return $total;
	}

	/**
	 * Part 2: Determine the number of safe reports with the Problem Dampener.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$total = 0;

		foreach ( $this->data as $levels ) {
			// Check if the report is already safe
			if ( $this->is_safe( $levels ) ) {
				$total ++;
				continue;
			}

			// Try removing each level and check if the report becomes safe
			for ( $i = 0; $i < count( $levels ); $i ++ ) {
				$levels_copy = $levels;
				unset( $levels_copy[ $i ] ); // Remove one

				if ( $this->is_safe( array_values( $levels_copy ) ) ) {
					$total ++;
					break;
				}
			}
		}

		return $total;
	}

	/**
	 * Checks if a report is safe based on the levels.
	 *
	 * @param array $levels The levels to check.
	 *
	 * @return bool True if the report is safe, false otherwise.
	 */
	private function is_safe( array $levels ): bool {
		$is_increasing     = true;
		$is_decreasing     = true;
		$valid_differences = true;

		for ( $i = 1; $i < count( $levels ); $i ++ ) {
			$diff = $levels[ $i ] - $levels[ $i - 1 ];

			// Check if levels differ by at least one and at most three
			if ( abs( $diff ) < 1 || abs( $diff ) > 3 ) {
				$valid_differences = false;
				break;
			}

			// Is it increasing or decreasing?
			if ( $diff > 0 ) {
				$is_decreasing = false;
			} elseif ( $diff < 0 ) {
				$is_increasing = false;
			}
		}

		return $valid_differences && ( $is_increasing || $is_decreasing );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-02-test.txt' : '/data/day-02.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		// Convert each line into an array of integers
		return array_map( fn( $line ) => array_map( 'intval', explode( ' ', $line ) ), $lines );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param int $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day02  = new Day02( $test, $part );
	$result = $day02->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 2,
			'real' => 526,
		],
		2 => [
			'test' => 4,
			'real' => 566,
		],
	];

	printf( PHP_EOL );
	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $test ? $expected_values[ $part ]['test'] : $expected_values[ $part ]['real'] );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
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
