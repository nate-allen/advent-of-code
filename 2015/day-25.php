<?php

namespace AdventOfCode\Year2015;

/**
 * Day 25: Let It Snow
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
	 * Find the code at the given row and column.
	 *
	 * @return integer
	 */
	public function run(): int {
		$row = $this->data[0];
		$col = $this->data[1];

		// Convert row, col to the sequence number.
		// Diagonal number (1-indexed) = row + col - 1.
		// Position at start of that diagonal = triangle(diag - 1) + 1.
		// Position within diagonal = col.
		$diag = $row + $col - 1;
		$n    = ( $diag * ( $diag - 1 ) ) / 2 + $col;

		// Generate the code by iterating the sequence.
		$code = 20151125;

		for ( $i = 1; $i < $n; $i++ ) {
			$code = ( $code * 252533 ) % 33554393;
		}

		return $code;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array [row, col]
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-25-test.txt' : '/data/day-25.txt';
		$text = trim( file_get_contents( __DIR__ . $file ) );

		preg_match( '/row (\d+), column (\d+)/', $text, $matches );

		return [ (int) $matches[1], (int) $matches[2] ];
	}
}

/**
 * Runs the puzzle and outputs results.
 *
 * @param bool $test Whether to use test data.
 */
function run_part( bool $test ): void {
	$start  = microtime( true );
	$day25  = new Day25( $test );
	$result = $day25->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		'test' => 27995004,
		'real' => 9132360,
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $test ? $expected_values['test'] : $expected_values['real'] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for test mode
while ( true ) {
	$test = strtolower( trim( readline( 'Do you want to run the test? (y/n): ' ) ) );
	if ( in_array( $test, [ 'y', 'n' ], true ) ) {
		$test_mode = $test === 'y';
		run_part( $test_mode );
		break;
	}
	echo 'Invalid input. Please enter y or n.' . PHP_EOL;
}
