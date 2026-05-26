<?php

namespace AdventOfCode\Year2017;

/**
 * Day 06: Memory Reallocation
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
	 * Part 1: Count redistribution cycles until a repeated state.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$banks = $this->data;
		$seen  = [];
		$steps = 0;
		$len   = count( $banks );

		while ( true ) {
			$key = implode( ',', $banks );

			if ( isset( $seen[ $key ] ) ) {
				return $steps;
			}

			$seen[ $key ] = true;

			$max_val = max( $banks );
			$max_idx = array_search( $max_val, $banks );

			$banks[ $max_idx ] = 0;
			for ( $i = 1; $i <= $max_val; $i++ ) {
				$banks[ ( $max_idx + $i ) % $len ]++;
			}

			$steps++;
		}
	}

	/**
	 * Part 2: Find the size of the redistribution loop.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$banks = $this->data;
		$seen  = [];
		$steps = 0;
		$len   = count( $banks );

		while ( true ) {
			$key = implode( ',', $banks );

			if ( isset( $seen[ $key ] ) ) {
				return $steps - $seen[ $key ];
			}

			$seen[ $key ] = $steps;

			$max_val = max( $banks );
			$max_idx = array_search( $max_val, $banks );

			$banks[ $max_idx ] = 0;
			for ( $i = 1; $i <= $max_val; $i++ ) {
				$banks[ ( $max_idx + $i ) % $len ]++;
			}

			$steps++;
		}
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
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( 'intval', preg_split( '/\s+/', $lines[0] ) );
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
			'test' => 5,
			'real' => 5042,
		],
		2 => [
			'test' => 4,
			'real' => 1086,
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
