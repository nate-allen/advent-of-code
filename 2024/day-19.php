<?php

namespace AdventOfCode\Year2024;

/**
 * Day 19: Linen Layout
 */
class Day19 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var int
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

	/**
	 * Cache for designs.
	 *
	 * @var array
	 */
	private array $cache;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return int
	 */
	public function run(): int {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Determine the number of designs that can be formed.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		[ $patterns, $designs ] = $this->data;

		$possible_count = 0;

		foreach ( $designs as $design ) {
			if ( $this->can_form_design( $design, $patterns ) ) {
				$possible_count ++;
			}
		}

		return $possible_count;
	}

	/**
	 * Part 2: Determine the total number of ways to form all designs.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		[ $patterns, $designs ] = $this->data;

		$total_ways = 0;

		foreach ( $designs as $design ) {
			$total_ways += $this->count_ways_to_form_design( $design, $patterns );
		}

		return $total_ways;
	}

	/**
	 * Checks if a design can be formed using the available patterns.
	 *
	 * @param string $design   The design to check.
	 * @param array  $patterns The available towel patterns.
	 *
	 * @return bool
	 */
	private function can_form_design( string $design, array $patterns ): bool {
		if ( $design === '' ) {
			return true;
		}

		// Check the cache.
		if ( isset( $this->cache[ $design ] ) ) {
			return $this->cache[ $design ];
		}

		// Try each pattern to see if it matches the start of the design.
		foreach ( $patterns as $pattern ) {
			if ( str_starts_with( $design, $pattern ) ) {
				$remaining_design = substr( $design, strlen( $pattern ) );
				if ( $this->can_form_design( $remaining_design, $patterns ) ) {
					return $this->cache[ $design ] = true; // Cache it and return true.
				}
			}
		}

		return $this->cache[ $design ] = false; // Cache it and return false.
	}

	/**
	 * Counts the number of ways a design can be formed using the available patterns.
	 *
	 * @param string $design   The design to check.
	 * @param array  $patterns The available towel patterns.
	 *
	 * @return int
	 */
	private function count_ways_to_form_design( string $design, array $patterns ): int {
		if ( $design === '' ) {
			return 1;
		}

		// Check the cache.
		if ( isset( $this->cache[ $design ] ) ) {
			return $this->cache[ $design ];
		}

		$ways = 0;

		// Try each pattern to see if it matches the start of the design
		foreach ( $patterns as $pattern ) {
			if ( str_starts_with( $design, $pattern ) ) {
				$remaining_design = substr( $design, strlen( $pattern ) );

				$ways += $this->count_ways_to_form_design( $remaining_design, $patterns );
			}
		}

		return $this->cache[ $design ] = $ways; // Cache and return total ways
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-19-test.txt' : '/data/day-19.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$patterns = explode( ', ', $lines[0] );
		$designs  = array_slice( $lines, 2 );

		usort( $patterns, fn( $a, $b ) => strlen( $b ) <=> strlen( $a ) );

		return [ $patterns, $designs ];
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param int $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day19  = new Day19( $test, $part );
	$result = $day19->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 6,
			'real' => 206,
		],
		2 => [
			'test' => 16,
			'real' => 622121814629343,
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
