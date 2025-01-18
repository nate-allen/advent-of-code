<?php

namespace AdventOfCode\Year2020;

/**
 * Day 09: Encoding Error
 */
class Day09 {
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
	 * Part 1: Find the first number that is not the sum of two of the 25 numbers before it.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$preamble = $this->is_test ? 5 : 25;
		$numbers  = $this->data;

		for ( $i = $preamble; $i < count( $numbers ); $i ++ ) {
			$found = false;

			for ( $j = $i - $preamble; $j < $i; $j ++ ) {
				for ( $k = $j + 1; $k < $i; $k ++ ) {
					if ( $numbers[ $j ] + $numbers[ $k ] === $numbers[ $i ] ) {
						$found = true;
						break 2;
					}
				}
			}

			if ( ! $found ) {
				return $numbers[ $i ];
			}
		}

		return 0;
	}

	/**
	 * Part 2: Find a contiguous set of at least two numbers that sum to the invalid number from Part 1.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$invalid_number = $this->solve_part_1();
		$numbers        = $this->data;
		$length         = count( $numbers );

		for ( $i = 0; $i < $length; $i ++ ) {
			$sum = $numbers[ $i ];

			for ( $j = $i + 1; $j < $length; $j ++ ) {
				$sum += $numbers[ $j ];

				if ( $sum === $invalid_number ) {
					$range = array_slice( $numbers, $i, $j - $i + 1 );

					return min( $range ) + max( $range );
				}

				if ( $sum > $invalid_number ) {
					break;
				}
			}
		}

		// This shouldn't happen if the input is correct.
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
		$file  = $test ? '/data/day-09-test.txt' : '/data/day-09.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( 'intval', $lines );
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
	$day09  = new Day09( $test, $part );
	$result = $day09->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 127,
			'real' => 144381670,
		],
		2 => [
			'test' => 62,
			'real' => 20532569,
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
