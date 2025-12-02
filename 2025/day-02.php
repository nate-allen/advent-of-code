<?php

namespace AdventOfCode\Year2025;

/**
 * Day 02: Gift Shop
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
	 * Array of ranges, where each range is ['start' => int, 'end' => int].
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
	 * Part 1: Find all invalid product IDs (made of digits repeated twice) and sum them.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$sum = 0;

		foreach ( $this->data as $range ) {
			// Check each ID in the range
			for ( $id = $range['start']; $id <= $range['end']; $id++ ) {
				if ( $this->is_invalid_id( $id ) ) {
					$sum += $id;
				}
			}
		}

		return $sum;
	}

	/**
	 * Part 2: Find all invalid product IDs (made of digits repeated at least twice) and sum them.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$sum = 0;

		foreach ( $this->data as $range ) {
			// Check each ID in the range
			for ( $id = $range['start']; $id <= $range['end']; $id++ ) {
				if ( $this->is_invalid_id( $id ) ) {
					$sum += $id;
				}
			}
		}

		return $sum;
	}

	/**
	 * Checks if an ID is invalid based on the current part.
	 * Part 1: Made of digits repeated exactly twice.
	 * Part 2: Made of digits repeated at least twice.
	 *
	 * @param int $id The product ID to check.
	 *
	 * @return bool True if invalid, false otherwise.
	 */
	private function is_invalid_id( int $id ): bool {
		$id_str = (string) $id;
		$length = strlen( $id_str );

		if ( $this->part === 1 ) {
			// Must have even length to be split into two equal halves
			if ( $length % 2 !== 0 ) {
				return false;
			}

			$half_length = $length / 2;
			$first_half  = substr( $id_str, 0, $half_length );
			$second_half = substr( $id_str, $half_length );

			// Check if first half equals second half
			return $first_half === $second_half;
		}

		// Part 2: Try different segment lengths
		for ( $segment_length = 1; $segment_length <= $length / 2; $segment_length++ ) {
			// Check if the length is divisible by the segment length
			if ( $length % $segment_length !== 0 ) {
				continue;
			}

			$num_segments = $length / $segment_length;

			// We need at least 2 repetitions
			if ( $num_segments < 2 ) {
				continue;
			}

			// Get the first segment and check if repeating it forms the entire string
			$first_segment = substr( $id_str, 0, $segment_length );
			if ( str_repeat( $first_segment, $num_segments ) === $id_str ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of ranges, where each range is ['start' => int, 'end' => int].
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-02-test.txt' : '/data/day-02.txt';
		$input = trim( file_get_contents( __DIR__ . $file ) );

		// Split ranges by comma
		$range_strings = explode( ',', $input );

		// Parse each range into start and end values
		$ranges = [];
		foreach ( $range_strings as $range_string ) {
			[ $start, $end ] = explode( '-', $range_string );
			$ranges[]        = [
				'start' => (int) $start,
				'end'   => (int) $end,
			];
		}

		return $ranges;
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
			'test' => 1227775554,
			'real' => 23039913998,
		],
		2 => [
			'test' => 4174379265,
			'real' => 35950619148,
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
