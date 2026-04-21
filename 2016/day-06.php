<?php

namespace AdventOfCode\Year2016;

/**
 * Day 06: Signals and Noise
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
	 * @return string
	 */
	public function run(): string {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Decode message using most common character per column.
	 *
	 * @return string
	 */
	private function solve_part_1(): string {
		return $this->decode( 'arsort' );
	}

	/**
	 * Part 2: Decode message using least common character per column.
	 *
	 * @return string
	 */
	private function solve_part_2(): string {
		return $this->decode( 'asort' );
	}

	/**
	 * Decodes the message by sorting character frequencies per column.
	 *
	 * @param callable $sort_fn Sorting function (arsort for most common, asort for least).
	 *
	 * @return string
	 */
	private function decode( callable $sort_fn ): string {
		$cols    = strlen( $this->data[0] );
		$message = '';

		for ( $c = 0; $c < $cols; $c++ ) {
			$counts = [];
			foreach ( $this->data as $line ) {
				$ch = $line[ $c ];
				$counts[ $ch ] = ( $counts[ $ch ] ?? 0 ) + 1;
			}
			$sort_fn( $counts );
			$message .= array_key_first( $counts );
		}

		return $message;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-06-test.txt' : '/data/day-06.txt';

		return explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
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
			'test' => 'easter',
			'real' => 'gebzfnbt',
		],
		2 => [
			'test' => 'advent',
			'real' => 'fykjtwyn',
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
