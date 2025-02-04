<?php

namespace AdventOfCode\Year2020;

/**
 * Day 25: Combo Breaker
 */
class Day25 {
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

	public function __construct( bool $test ) {
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
	}

	/**
	 * Determine the encryption key by finding the loop size.
	 *
	 * @return integer
	 */
	public function run(): int {
		[ $card_public_key, $door_public_key ] = $this->data;

		// Find the card's loop size.
		// Start with a value of 1 and repeatedly transform it by multiplying by 7 modulo 20201227.
		$value          = 1;
		$card_loop_size = 0;

		while ( $value !== $card_public_key ) {
			$value = ( $value * 7 ) % 20201227;
			$card_loop_size ++;
		}

		// Using the door's public key and the card's loop size, compute the encryption key.
		return bcpowmod( $door_public_key, $card_loop_size, 20201227 );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-25-test.txt' : '/data/day-25.txt';
		$lines = array_map( 'intval', explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) ) );

		return $lines;
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 * @param bool    $test Whether to use test data.
 */
function run_part( bool $test ): void {
	$start  = microtime( true );
	$day25  = new Day25( $test );
	$result = $day25->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		'test' => 14897079,
		'real' => 4441893,
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $test ? $expected_values['test'] : $expected_values['real'] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for part and test mode
while ( true ) {
	$test = strtolower( trim( readline( 'Do you want to run the test? (y/n): ' ) ) );
	if ( in_array( $test, [ 'y', 'n' ], true ) ) {
		$test_mode = $test === 'y';
		run_part( $test_mode );
		break;
	}
	echo 'Invalid input. Please enter y or n.' . PHP_EOL;
}