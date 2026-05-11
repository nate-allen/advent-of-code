<?php

namespace AdventOfCode\Year2016;

/**
 * Day 18: Like a Rogue
 */
class Day18 {
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
	 * The first row of tiles.
	 *
	 * @var string
	 */
	private string $data;

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
	 * Part 1: Count safe tiles in 40 rows.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$rows = $this->is_test ? 10 : 40;

		return $this->count_safe( $rows );
	}

	/**
	 * Part 2: Count safe tiles in 400000 rows.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->count_safe( 400000 );
	}

	/**
	 * Counts safe tiles over a given number of rows.
	 *
	 * A tile is a trap if left XOR right from the previous row.
	 *
	 * @param int $rows Number of rows.
	 *
	 * @return integer
	 */
	private function count_safe( int $rows ): int {
		$row  = $this->data;
		$len  = strlen( $row );
		$safe = substr_count( $row, '.' );

		for ( $r = 1; $r < $rows; $r++ ) {
			$next = '';

			for ( $i = 0; $i < $len; $i++ ) {
				$left  = $i > 0 ? $row[ $i - 1 ] : '.';
				$right = $i < $len - 1 ? $row[ $i + 1 ] : '.';
				$next .= $left !== $right ? '^' : '.';
			}

			$safe += substr_count( $next, '.' );
			$row   = $next;
		}

		return $safe;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return string The first row.
	 */
	private function parse_data( bool $test ): string {
		$file = $test ? '/data/day-18-test.txt' : '/data/day-18.txt';

		return trim( file_get_contents( __DIR__ . $file ) );
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
	$day18  = new Day18( $test, $part );
	$result = $day18->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 38,
			'real' => 1961,
		],
		2 => [
			'test' => 0,
			'real' => 20000795,
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
