<?php

namespace AdventOfCode\Year2018;

/**
 * Day 03: No Matter How You Slice It
 */
class Day03 {
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
	 * Part 1: Count square inches within two or more claims
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$fabric = $this->build_fabric_map();

		// Count squares covered by 2 or more claims
		$overlap_count = 0;
		foreach ( $fabric as $count ) {
			if ( $count >= 2 ) {
				$overlap_count++;
			}
		}

		return $overlap_count;
	}

	/**
	 * Part 2: Find the ID of the claim that doesn't overlap with any other claim
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$fabric = $this->build_fabric_map();

		// Identify overlapped squares
		$overlapped = [];
		foreach ( $fabric as $key => $count ) {
			if ( $count >= 2 ) {
				$overlapped[$key] = true;
			}
		}

		// Find claim with no overlaps
		foreach ( $this->data as $claim ) {
			[ $id, $left, $top, $width, $height ] = $claim;
			$has_overlap                          = false;

			for ( $x = $left; $x < $left + $width; $x++ ) {
				for ( $y = $top; $y < $top + $height; $y++ ) {
					$key = "$x,$y";
					if ( isset( $overlapped[$key] ) ) {
						$has_overlap = true;
						break 2; // Break out of both loops
					}
				}
			}

			if ( ! $has_overlap ) {
				return $id;
			}
		}

		return 0; // Should never reach here per problem statement
	}

	/**
	 * Builds a fabric map tracking how many claims cover each square inch.
	 *
	 * @return array Associative array where keys are "x,y" coordinates and values are claim counts
	 */
	private function build_fabric_map(): array {
		$fabric = [];

		// Process each claim
		foreach ( $this->data as $claim ) {
			[ $id, $left, $top, $width, $height ] = $claim;

			// Mark all squares covered by this claim
			for ( $x = $left; $x < $left + $width; $x++ ) {
				for ( $y = $top; $y < $top + $height; $y++ ) {
					$key          = "$x,$y";
					$fabric[$key] = ($fabric[$key] ?? 0) + 1;
				}
			}
		}

		return $fabric;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of claims, each as [id, left, top, width, height]
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-03-test.txt' : '/data/day-03.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$claims = [];
		foreach ( $lines as $line ) {
			// Extract all numbers from the line: #123 @ 3,2: 5x4
			// Pattern matches: id, left, top, width, height
			if ( preg_match_all( '/\d+/', $line, $matches ) ) {
				$numbers  = array_map( 'intval', $matches[0] );
				$claims[] = [
					$numbers[0], // ID
					$numbers[1], // left
					$numbers[2], // top
					$numbers[3], // width
					$numbers[4], // height
				];
			}
		}

		return $claims;
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
	$day03  = new Day03( $test, $part );
	$result = $day03->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 4,
			'real' => 110891,
		],
		2 => [
			'test' => 3,
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
