<?php

namespace AdventOfCode\Year2020;

/**
 * Day 02: Password Philosophy
 */
class Day02 {
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
	 * Part 1: The policy indicates the minimum and maximum number of times a given letter must appear for the password
	 *         to be valid. Check how many passwords are valid according to their policies.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$valid_passwords = 0;

		foreach ( $this->data as $item ) {
			// Count the occurrences of the letter in the password
			$count = substr_count( $item[2], $item[0] );

			// Check if the count is within the specified range
			if ( $count >= $item[1][0] && $count <= $item[1][1] ) {
				$valid_passwords++;
			}
		}

		return $valid_passwords;
	}

	/**
	 * Part 2: The policy indicates the positions where the letter must appear. Check how many passwords are valid
	 *         according to the new interpretation of the policies.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$valid_passwords = 0;

		foreach ( $this->data as $item ) {
			// Check if the letter appears at exactly one of the specified positions
			if ( ( $item[2][ $item[1][0] - 1 ] === $item[0] ) xor ( $item[2][ $item[1][1] - 1 ] === $item[0] ) ) {
				$valid_passwords ++;
			}
		}

		return $valid_passwords;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-02-test.txt' : '/data/day-02.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map(function($line) {
			preg_match( '/^(\\d+)-(\\d+) (\\w): (.+)$/', $line, $matches );

			// Extract the components
			return [
				$matches[3], // Letter
				[ (int) $matches[1], (int) $matches[2] ], // Min and Max
				$matches[4] // Password
			];
		}, $lines);
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
	$day02  = new Day02( $test, $part );
	$result = $day02->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 2,
			'real' => 422,
		],
		2 => [
			'test' => 1,
			'real' => 451,
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
