<?php

namespace AdventOfCode\Year2018;

/**
 * Day 05: Alchemical Reduction
 */
class Day05 {
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
	 * Parsed data from the input file (polymer string).
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
	 * Part 1: Count units remaining after fully reacting the polymer
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->react_polymer( $this->data );
	}

	/**
	 * Part 2: Find the shortest polymer length after removing all instances of one unit type
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$min_length = null;

		// Try removing each letter type (a-z)
		for ( $letter = 'a'; $letter <= 'z'; $letter++ ) {
			// Remove both uppercase and lowercase versions of the letter
			$filtered_polymer = str_replace( [ $letter, strtoupper( $letter ) ], '', $this->data );

			// React the filtered polymer
			$length = $this->react_polymer( $filtered_polymer );

			// Track the minimum length
			if ( $min_length === null || $length < $min_length ) {
				$min_length = $length;
			}
		}

		return $min_length;
	}

	/**
	 * Reacts a polymer string by removing adjacent units of the same type but opposite polarity.
	 * Uses a stack-based single-pass algorithm for O(n) time complexity.
	 *
	 * @param string $polymer The polymer string to react.
	 *
	 * @return integer The number of units remaining after all reactions.
	 */
	private function react_polymer( string $polymer ): int {
		$stack  = [];
		$length = strlen( $polymer );

		for ( $i = 0; $i < $length; $i++ ) {
			$char = $polymer[ $i ];

			// If stack is empty, push the character
			if ( empty( $stack ) ) {
				$stack[] = $char;
				continue;
			}

			// Get the top of the stack
			$top = $stack[ count( $stack ) - 1 ];

			// Check if characters react (same letter, opposite case)
			// Two characters react if: same letter (case-insensitive) but different case
			if ( strtolower( $char ) === strtolower( $top ) && $char !== $top ) {
				// They react - remove the top from stack
				array_pop( $stack );
			} else {
				// No reaction - push the character
				$stack[] = $char;
			}
		}

		return count( $stack );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return string
	 */
	private function parse_data( bool $test ): string {
		$file  = $test ? '/data/day-05-test.txt' : '/data/day-05.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		// Input is a single line polymer string
		return $lines[0];
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
	$day05  = new Day05( $test, $part );
	$result = $day05->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 10,
			'real' => 11590,
		],
		2 => [
			'test' => 4,
			'real' => 4504,
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
