<?php

namespace AdventOfCode\Year2025;

/**
 * Day 05: Day 5: Cafeteria
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
	 * Part 1: Count how many available ingredient IDs are fresh
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$ranges        = $this->data['ranges'];
		$available_ids = $this->data['available_ids'];
		$fresh_count   = 0;

		// Check each available ID
		foreach ( $available_ids as $id ) {
			// Check if ID falls within any range
			foreach ( $ranges as $range ) {
				if ( $id >= $range['min'] && $id <= $range['max'] ) {
					$fresh_count++;
					break; // No need to check other ranges for this ID
				}
			}
		}

		return $fresh_count;
	}

	/**
	 * Part 2: Count all fresh ingredient IDs by merging overlapping ranges
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$ranges = $this->data['ranges'];

		// Sort ranges by start value
		usort(
			$ranges,
			function ( $a, $b ) {
				return $a['min'] <=> $b['min'];
			}
		);

		// Merge overlapping ranges
		$merged = [ $ranges[0] ];

		foreach ( array_slice( $ranges, 1 ) as $range ) {
			$last_idx = count( $merged ) - 1;

			// Check if current range overlaps with last merged range
			if ( $range['min'] <= $merged[ $last_idx ]['max'] ) {
				// Extend the last merged range
				$merged[ $last_idx ]['max'] = max( $merged[ $last_idx ]['max'], $range['max'] );
			} else {
				// No overlap, add as new range
				$merged[] = $range;
			}
		}

		// Sum the lengths of all merged ranges
		$total = 0;
		foreach ( $merged as $range ) {
			$total += $range['max'] - $range['min'] + 1;
		}

		return $total;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-05-test.txt' : '/data/day-05.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$ranges         = [];
		$available_ids  = [];
		$parsing_ranges = true;

		foreach ( $lines as $line ) {
			// Empty line separates ranges from available IDs
			if ( empty( $line ) ) {
				$parsing_ranges = false;
				continue;
			}

			if ( $parsing_ranges ) {
				// Parse range format: X-Y
				$parts    = explode( '-', $line );
				$ranges[] = [
					'min' => (int) $parts[0],
					'max' => (int) $parts[1],
				];
			} else {
				// Parse available ingredient IDs
				$available_ids[] = (int) $line;
			}
		}

		return [
			'ranges'        => $ranges,
			'available_ids' => $available_ids,
		];
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
			'test' => 3,
			'real' => 509,
		],
		2 => [
			'test' => 14,
			'real' => 336790092076620,
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
