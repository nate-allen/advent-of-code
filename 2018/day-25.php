<?php

namespace AdventOfCode\Year2018;

require_once __DIR__ . '/unionfind.php';

/**
 * Day 25: Four-Dimensional Adventure
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
	 * Executes the puzzle.
	 *
	 * @return integer
	 */
	public function run(): int {
		return $this->solve_part_1();
	}

	/**
	 * Count the number of constellations.
	 *
	 * Two points are in the same constellation if their Manhattan distance
	 * is ≤ 3 or if they can form a chain of points, each within distance 3
	 * of the next.
	 *
	 * @return integer Number of constellations.
	 */
	private function solve_part_1(): int {
		$points = $this->data;
		$n      = count( $points );
		$uf     = new UnionFind( $n );

		// For each pair of points, if Manhattan distance ≤ 3, union them.
		for ( $i = 0; $i < $n; $i++ ) {
			for ( $j = $i + 1; $j < $n; $j++ ) {
				$distance = $this->manhattan_distance( $points[ $i ], $points[ $j ] );
				if ( $distance <= 3 ) {
					$uf->union( $i, $j );
				}
			}
		}

		return $uf->count_sets();
	}

	/**
	 * Calculate Manhattan distance between two 4D points.
	 *
	 * @param array $p1 First point [w, x, y, z].
	 * @param array $p2 Second point [w, x, y, z].
	 * 
	 * @return int Manhattan distance.
	 */
	private function manhattan_distance( array $p1, array $p2 ): int {
		return abs( $p1[0] - $p2[0] ) + abs( $p1[1] - $p2[1] ) + abs( $p1[2] - $p2[2] ) + abs( $p1[3] - $p2[3] );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of 4D points, each point is [w, x, y, z]
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-25-test.txt' : '/data/day-25.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$points = [];
		foreach ( $lines as $line ) {
			$coords = array_map( 'intval', explode( ',', trim( $line ) ) );
			if ( count( $coords ) === 4 ) {
				$points[] = $coords;
			}
		}

		return $points;
	}
}

/**
 * Runs the puzzle with the given settings and outputs results.
 *
 * @param bool $test Whether to use test data.
 */
function run_puzzle( bool $test ): void {
	$start  = microtime( true );
	$day25  = new Day25( $test );
	$result = $day25->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_value = $test ? 8 : 399;

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $expected_value );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for test mode
while ( true ) {
	$test = strtolower( trim( readline( 'Do you want to run the test? (y/n): ' ) ) );
	if ( in_array( $test, [ 'y', 'n' ], true ) ) {
		$test_mode = $test === 'y';
		run_puzzle( $test_mode );
		break;
	}
	echo 'Invalid input. Please enter y or n.' . PHP_EOL;
}
