<?php

namespace AdventOfCode\Year2016;

/**
 * Day 01: No Time for a Taxicab
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
	 * Part 1: Find Manhattan distance to final destination.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$x   = 0;
		$y   = 0;
		$dir = 0; // 0=N, 1=E, 2=S, 3=W
		$dx  = [ 0, 1, 0, -1 ];
		$dy  = [ 1, 0, -1, 0 ];

		foreach ( $this->data as $step ) {
			$dir = ( $dir + ( $step[0] === 'R' ? 1 : 3 ) ) % 4;
			$dist = (int) substr( $step, 1 );
			$x += $dx[ $dir ] * $dist;
			$y += $dy[ $dir ] * $dist;
		}

		return abs( $x ) + abs( $y );
	}

	/**
	 * Part 2: Find Manhattan distance to first location visited twice.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$x       = 0;
		$y       = 0;
		$dir     = 0;
		$dx      = [ 0, 1, 0, -1 ];
		$dy      = [ 1, 0, -1, 0 ];
		$visited = [ '0,0' => true ];

		foreach ( $this->data as $step ) {
			$dir  = ( $dir + ( $step[0] === 'R' ? 1 : 3 ) ) % 4;
			$dist = (int) substr( $step, 1 );

			for ( $i = 0; $i < $dist; $i++ ) {
				$x += $dx[ $dir ];
				$y += $dy[ $dir ];
				$key = "$x,$y";

				if ( isset( $visited[ $key ] ) ) {
					return abs( $x ) + abs( $y );
				}

				$visited[ $key ] = true;
			}
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
	private function parse_data( bool $test ): array {
		$file = $test ? "/data/day-01-test{$this->part}.txt" : '/data/day-01.txt';

		return array_map( 'trim', explode( ',', trim( file_get_contents( __DIR__ . $file ) ) ) );
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
			'test' => 12,
			'real' => 298,
		],
		2 => [
			'test' => 4,
			'real' => 158,
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
