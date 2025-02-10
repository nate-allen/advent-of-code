<?php

namespace AdventOfCode\Year2019;

/**
 * Day 04: Secure Container
 */
class Day04 {
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
	 * Part 1: Determine the number of valid passwords between the given range.
	 *
	 * Password Rules:
	 * 1) The password must be a six-digit number.
	 * 2) The value must be within the given range.
	 * 3) The password must contain at least one double digit.
	 * 4) The digits must not decrease from left to right.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		[ $min, $max ] = $this->data;
		$valid_passwords = 0;

		for ( $i = $min; $i <= $max; $i ++ ) {
			if ( $this->validate_password( $i ) ) {
				$valid_passwords ++;
			}
		}

		return $valid_passwords;
	}

	/**
	 * Part 2: Determine the number of valid passwords between the given range.
	 *
	 * Password Rules:
	 * 1) The password must be a six-digit number.
	 * 2) The value must be within the given range.
	 * 3) The password must contain at least one double digit.
	 * 4) The digits must not decrease from left to right.
	 * 5) The double digit must not be part of a larger group of matching digits.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		[ $min, $max ] = $this->data;
		$valid_passwords = 0;

		for ( $i = $min; $i <= $max; $i ++ ) {
			if ( $this->validate_password( $i ) ) {
				$valid_passwords ++;
			}
		}

		return $valid_passwords;
	}

	/**
	 * Validates a password according to the rules.
	 *
	 * @param int $password The password candidate.
	 *
	 * @return bool True if the password meets the criteria.
	 */
	private function validate_password( int $password ): bool {
		// Convert the number to an array of its digits.
		$digits     = str_split( (string) $password );
		$num_digits = count( $digits );

		// First, check that digits never decrease.
		for ( $i = 0; $i < $num_digits - 1; $i ++ ) {
			if ( $digits[ $i ] > $digits[ $i + 1 ] ) {
				return false;
			}
		}

		if ( $this->part === 1 ) {
			// Check for at least one adjacent pair.
			for ( $i = 0; $i < $num_digits - 1; $i ++ ) {
				if ( $digits[ $i ] === $digits[ $i + 1 ] ) {
					return true;
				}
			}

			return false;
		} elseif ( $this->part === 2 ) {
			// Check for an adjacent pair that is not part of a larger group.
			$group_counts  = [];
			$current_digit = $digits[0];
			$current_count = 1;

			for ( $i = 1; $i < $num_digits; $i ++ ) {
				if ( $digits[ $i ] === $current_digit ) {
					$current_count ++;
				} else {
					$group_counts[] = $current_count;
					$current_digit  = $digits[ $i ];
					$current_count  = 1;
				}
			}
			// Add the count for the last group.
			$group_counts[] = $current_count;

			// Check if any group is exactly 2.
			return in_array( 2, $group_counts, true );
		}

		return false;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-04-test.txt' : '/data/day-04.txt';

		return array_map( 'intval', explode( "-", trim( file_get_contents( __DIR__ . $file ) ) ) );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day04  = new Day04( $test, $part );
	$result = $day04->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 45,
			'real' => 475,
		],
		2 => [
			'test' => 8,
			'real' => 297,
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
