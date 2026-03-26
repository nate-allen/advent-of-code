<?php

namespace AdventOfCode\Year2015;

/**
 * Day 11: Corporate Policy
 */
class Day11 {
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
	 * Part 1: Find the next valid password.
	 *
	 * @return string
	 */
	private function solve_part_1(): string {
		$password = $this->data;

		do {
			$password = $this->increment( $password );
		} while ( ! $this->is_valid( $password ) );

		return $password;
	}

	/**
	 * Part 2: Find the next valid password after part 1's result.
	 *
	 * @return string
	 */
	private function solve_part_2(): string {
		$password = $this->solve_part_1();

		do {
			$password = $this->increment( $password );
		} while ( ! $this->is_valid( $password ) );

		return $password;
	}

	/**
	 * Increments a password string, skipping i, o, l.
	 *
	 * @param string $password The password to increment.
	 *
	 * @return string
	 */
	private function increment( string $password ): string {
		$chars = str_split( $password );

		for ( $i = count( $chars ) - 1; $i >= 0; $i-- ) {
			$chars[ $i ] = chr( ord( $chars[ $i ] ) + 1 );

			// Skip forbidden letters.
			if ( in_array( $chars[ $i ], [ 'i', 'o', 'l' ], true ) ) {
				$chars[ $i ] = chr( ord( $chars[ $i ] ) + 1 );
				// Fill rest with 'a' since we skipped ahead.
				for ( $j = $i + 1; $j < count( $chars ); $j++ ) {
					$chars[ $j ] = 'a';
				}
				break;
			}

			if ( $chars[ $i ] <= 'z' ) {
				break;
			}

			$chars[ $i ] = 'a';
		}

		return implode( '', $chars );
	}

	/**
	 * Checks if a password meets all requirements.
	 *
	 * @param string $password The password to validate.
	 *
	 * @return bool
	 */
	private function is_valid( string $password ): bool {
		// No i, o, or l.
		if ( preg_match( '/[iol]/', $password ) ) {
			return false;
		}

		// Must have an increasing straight of 3.
		$has_straight = false;
		for ( $i = 0; $i < strlen( $password ) - 2; $i++ ) {
			if ( ord( $password[ $i + 1 ] ) === ord( $password[ $i ] ) + 1 &&
				ord( $password[ $i + 2 ] ) === ord( $password[ $i ] ) + 2 ) {
				$has_straight = true;
				break;
			}
		}
		if ( ! $has_straight ) {
			return false;
		}

		// Must have at least two different pairs.
		preg_match_all( '/(.)\1/', $password, $matches );

		return count( array_unique( $matches[1] ) ) >= 2;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return string
	 */
	private function parse_data( bool $test ): string {
		$file = $test ? '/data/day-11-test.txt' : '/data/day-11.txt';

		return trim( file_get_contents( __DIR__ . $file ) );
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
	$day11  = new Day11( $test, $part );
	$result = $day11->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 'abcdffaa',
			'real' => 'hepxxyzz',
		],
		2 => [
			'test' => 'abcdffaa',
			'real' => 'heqaabcc',
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
