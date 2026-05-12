<?php

namespace AdventOfCode\Year2016;

/**
 * Day 19: An Elephant Named Joseph
 */
class Day19 {
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
	 * Number of elves.
	 *
	 * @var int
	 */
	private int $data;

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
	 * Part 1: Josephus problem - steal from left neighbor.
	 *
	 * Formula: 2 * (n - 2^floor(log2(n))) + 1
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$n     = $this->data;
		$pow   = 1;

		while ( $pow * 2 <= $n ) {
			$pow *= 2;
		}

		return 2 * ( $n - $pow ) + 1;
	}

	/**
	 * Part 2: Steal from the elf directly across the circle.
	 *
	 * Pattern based on powers of 3:
	 * - If n == p (highest power of 3 <= n): answer is n
	 * - If n - p <= p: answer is n - p
	 * - If n - p > p: answer is 2*(n-p) - p
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$n   = $this->data;
		$pow = 1;

		while ( $pow * 3 <= $n ) {
			$pow *= 3;
		}

		if ( $n === $pow ) {
			return $n;
		}

		if ( $n - $pow <= $pow ) {
			return $n - $pow;
		}

		return 2 * ( $n - $pow ) - $pow;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return int Number of elves.
	 */
	private function parse_data( bool $test ): int {
		$file = $test ? '/data/day-19-test.txt' : '/data/day-19.txt';

		return (int) trim( file_get_contents( __DIR__ . $file ) );
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
	$day19  = new Day19( $test, $part );
	$result = $day19->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 3,
			'real' => 1815603,
		],
		2 => [
			'test' => 2,
			'real' => 1410630,
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
