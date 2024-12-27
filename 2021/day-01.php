<?php

namespace AdventOfCode\Year2021;

/**
 * Day 01: Sonar Sweep
 */
class Day01 {
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
	 * Part 1: How many measurements are larger than the previous measurement?
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$increases = 0;

		// Loop through the data and compare each measurement with the previous one
		for ( $i = 1; $i < count( $this->data ); $i++ ) {
			if ( $this->data[ $i ] > $this->data[ $i - 1 ] ) {
				$increases++;
			}
		}

		return $increases;
	}

	/**
	 * Part 2: Count the number of times the sum of measurements in a three-measurement sliding window increases.
	 * How many sums are larger than the previous sum?
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$total_times = 0;
		$prev_sum = $this->data[0] + $this->data[1] + $this->data[2];

		// Loop through the data and compare each sum of three measurements with the previous sum, starting from the 4th measurement
		for ( $i = 3; $i < count( $this->data ); $i++ ) {
			$sum = $this->data[ $i - 2 ] + $this->data[ $i - 1 ] + $this->data[ $i ];

			// Increment the counter if the sum is larger than the previous sum
			if ( $sum > $prev_sum ) {
				$total_times++;
			}

			$prev_sum = $sum;
		}

		return $total_times;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-01-test.txt' : '/data/day-01.txt';
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
	$day01  = new Day01( $test, $part );
	$result = $day01->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 7,
			'real' => 1676,
		],
		2 => [
			'test' => 5,
			'real' => 1706,
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
