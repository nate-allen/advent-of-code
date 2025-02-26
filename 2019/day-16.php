<?php

namespace AdventOfCode\Year2019;

/**
 * Day 16: Flawed Frequency Transmission
 */
class Day16 {
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
	 * Part 1: Compute the first eight digits of the output after 100 phases of FFT.
	 *
	 * @return int The first eight digits of the final output list as an integer.
	 */
	private function solve_part_1(): int {
		$digits       = $this->data;
		$base_pattern = [ 0, 1, 0, -1 ];
		$length       = count( $digits );

		// Perform 100 phases of FFT transformation.
		for ( $phase = 0; $phase < 100; $phase++ ) {
			$new_digits = [];
			for ( $i = 0; $i < $length; $i++ ) {
				$sum = 0;
				for ( $j = 0; $j < $length; $j++ ) {
					$pattern_index = intdiv( $j + 1, $i + 1 ) % 4;
					$sum          += $digits[ $j ] * $base_pattern[ $pattern_index ];
				}
				$new_digits[ $i ] = abs( $sum ) % 10;
			}
			$digits = $new_digits;
		}

		// Concatenate the first eight digits into a single number.
		return (int) implode( '', array_slice( $digits, 0, 8 ) );
	}

	/**
	 * Part 2: Compute the eight-digit message embedded in the final output list after 100 phases.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		// Get the base signal.
		$base_signal = $this->data;
		$base_length = count( $base_signal );

		// Determine the message offset from the first seven digits.
		$offset = (int) implode( '', array_slice( $base_signal, 0, 7 ) );
		$total_length = $base_length * 10000;

		// Build only the effective signal starting at the offset.
		$effective_length = $total_length - $offset;
		$signal         = [];

		for ( $i = 0; $i < $effective_length; $i++ ) {
			$signal[ $i ] = $base_signal[ ( $offset + $i ) % $base_length ];
		}

		// Each digit is updated by summing itself with the digit to its right, processing backwards through the array.
		for ( $phase = 0; $phase < 100; $phase++ ) {
			for ( $i = $effective_length - 2; $i >= 0; $i-- ) {
				$signal[ $i ] = ( $signal[ $i ] + $signal[ $i + 1 ] ) % 10;
			}
		}

		// Return the first eight digits of the final output list
		return (int) implode( '', array_slice( $signal, 0, 8 ) );
	}

	/**
	 * Parses the puzzle input data and returns it as an array of integers.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array<int>
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-16-test.txt' : '/data/day-16.txt';

		return array_map( 'intval', str_split( trim( file_get_contents( __DIR__ . $file ) ) ) );
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
	$day16  = new Day16( $test, $part );
	$result = $day16->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 52486276,
			'real' => 42945143,
		],
		2 => [
			'test' => 53553731,
			'real' => 99974970,
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
