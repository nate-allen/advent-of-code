<?php

namespace AdventOfCode\Year2015;

/**
 * Day 09: All in a Single Night
 */
class Day09 {
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
	 * Parsed data: distance map.
	 *
	 * @var array
	 */
	private array $data;

	/**
	 * List of unique cities.
	 *
	 * @var array
	 */
	private array $cities;

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
	 * Part 1: Find the shortest route visiting all cities.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$distances = $this->all_route_distances();

		return min( $distances );
	}

	/**
	 * Part 2: Find the longest route visiting all cities.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$distances = $this->all_route_distances();

		return max( $distances );
	}

	/**
	 * Calculates the total distance for every permutation of cities.
	 *
	 * @return array
	 */
	private function all_route_distances(): array {
		$distances = [];

		foreach ( $this->permutations( $this->cities ) as $route ) {
			$dist = 0;
			for ( $i = 0; $i < count( $route ) - 1; $i++ ) {
				$dist += $this->data[ $route[ $i ] ][ $route[ $i + 1 ] ];
			}
			$distances[] = $dist;
		}

		return $distances;
	}

	/**
	 * Generates all permutations of the given array.
	 *
	 * @param array $items Items to permute.
	 *
	 * @return \Generator
	 */
	private function permutations( array $items ): \Generator {
		if ( count( $items ) <= 1 ) {
			yield $items;
			return;
		}

		for ( $i = 0; $i < count( $items ); $i++ ) {
			$rest = array_merge( array_slice( $items, 0, $i ), array_slice( $items, $i + 1 ) );
			foreach ( $this->permutations( $rest ) as $perm ) {
				yield array_merge( [ $items[ $i ] ], $perm );
			}
		}
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-09-test.txt' : '/data/day-09.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$dist  = [];
		$cities = [];

		foreach ( $lines as $line ) {
			preg_match( '/(\w+) to (\w+) = (\d+)/', $line, $m );
			$dist[ $m[1] ][ $m[2] ] = (int) $m[3];
			$dist[ $m[2] ][ $m[1] ] = (int) $m[3];
			$cities[ $m[1] ] = true;
			$cities[ $m[2] ] = true;
		}

		$this->cities = array_keys( $cities );

		return $dist;
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
	$day09  = new Day09( $test, $part );
	$result = $day09->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 605,
			'real' => 251,
		],
		2 => [
			'test' => 982,
			'real' => 898,
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
