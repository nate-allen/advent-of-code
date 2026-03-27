<?php

namespace AdventOfCode\Year2015;

/**
 * Day 12: JSAbacusFramework.io
 */
class Day12 {
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
	 * Raw JSON string.
	 *
	 * @var string
	 */
	private string $raw;

	/**
	 * Parsed JSON data.
	 *
	 * @var mixed
	 */
	private mixed $data;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->parse_data( $this->is_test );
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
	 * Part 1: Sum all numbers in the JSON document.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		preg_match_all( '/-?\d+/', $this->raw, $matches );

		return array_sum( $matches[0] );
	}

	/**
	 * Part 2: Sum all numbers, ignoring objects with any "red" value.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->sum_no_red( $this->data );
	}

	/**
	 * Recursively sums numbers, skipping objects containing "red" values.
	 *
	 * @param mixed $node The JSON node to process.
	 *
	 * @return integer
	 */
	private function sum_no_red( mixed $node ): int {
		if ( is_int( $node ) || is_float( $node ) ) {
			return (int) $node;
		}

		if ( is_array( $node ) ) {
			return array_sum( array_map( [ $this, 'sum_no_red' ], $node ) );
		}

		if ( is_object( $node ) ) {
			$values = get_object_vars( $node );

			if ( in_array( 'red', $values, true ) ) {
				return 0;
			}

			return array_sum( array_map( [ $this, 'sum_no_red' ], $values ) );
		}

		return 0;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): void {
		$file      = $test ? '/data/day-12-test.txt' : '/data/day-12.txt';
		$this->raw = trim( file_get_contents( __DIR__ . $file ) );
		$this->data = json_decode( $this->raw );
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
	$day12  = new Day12( $test, $part );
	$result = $day12->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 6,
			'real' => 156366,
		],
		2 => [
			'test' => 6,
			'real' => 96852,
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
