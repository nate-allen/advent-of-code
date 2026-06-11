<?php

namespace AdventOfCode\Year2017;

/**
 * Day 17: TITLE HERE
 */
class Day17 {
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
	 * Part 1: SHORT_DESCRIPTION_HERE
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$steps  = $this->data;
		$buffer = [ 0 ];
		$pos    = 0;

		for ( $i = 1; $i <= 2017; $i++ ) {
			$pos = ( $pos + $steps ) % count( $buffer ) + 1;
			array_splice( $buffer, $pos, 0, [ $i ] );
		}

		return $buffer[ $pos + 1 ];
	}

	/**
	 * Part 2: SHORT_DESCRIPTION_HERE
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$steps       = $this->data;
		$pos         = 0;
		$after_zero  = 0;

		for ( $i = 1; $i <= 50000000; $i++ ) {
			$pos = ( $pos + $steps ) % $i + 1;
			if ( $pos === 1 ) {
				$after_zero = $i;
			}
		}

		return $after_zero;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): int {
		$file = $test ? '/data/day-17-test.txt' : '/data/day-17.txt';

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
	$day17  = new Day17( $test, $part );
	$result = $day17->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 638,
			'real' => 1282,
		],
		2 => [
			'test' => 0,
			'real' => 27650600,
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
