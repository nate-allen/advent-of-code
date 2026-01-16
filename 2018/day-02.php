<?php

namespace AdventOfCode\Year2018;

/**
 * Day 02: Inventory Management System
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
	 * @return int|string
	 */
	public function run(): int|string {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Calculate checksum by counting boxes with exactly 2 and 3 of any letter.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$count_with_two   = 0;
		$count_with_three = 0;

		foreach ( $this->data as $box_id ) {
			// Count frequency of each character
			$char_counts = array_count_values( str_split( $box_id ) );
			$frequencies = array_values( $char_counts );

			// Check if any letter appears exactly 2 or 3 times
			if ( in_array( 2, $frequencies, true ) ) {
				++$count_with_two;
			}
			if ( in_array( 3, $frequencies, true ) ) {
				++$count_with_three;
			}
		}

		return $count_with_two * $count_with_three;
	}

	/**
	 * Part 2: Find two box IDs that differ by exactly one character and return common letters.
	 *
	 * @return string
	 */
	private function solve_part_2(): string {
		$count = count( $this->data );

		// Compare each box ID with every other box ID
		for ( $i = 0; $i < $count; $i++ ) {
			for ( $j = $i + 1; $j < $count; $j++ ) {
				$id1 = $this->data[ $i ];
				$id2 = $this->data[ $j ];

				// Count differences
				$diff_count = 0;
				$length     = strlen( $id1 );
				$common     = '';

				for ( $k = 0; $k < $length; $k++ ) {
					if ( $id1[ $k ] !== $id2[ $k ] ) {
						++$diff_count;
					} else {
						$common .= $id1[ $k ];
					}
				}

				// If they differ by exactly one character, return the common letters
				if ( $diff_count === 1 ) {
					return $common;
				}
			}
		}

		return '';
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? "/data/day-02-test{$this->part}.txt" : '/data/day-02.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return $lines;
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
			'test' => 12,
			'real' => 6723,
		],
		2 => [
			'test' => 'fgij',
			'real' => 'prtkqyluiusocwvaezjmhmfgx',
		],
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	$expected = $test ? $expected_values[ $part ]['test'] : $expected_values[ $part ]['real'];

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $expected );
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
