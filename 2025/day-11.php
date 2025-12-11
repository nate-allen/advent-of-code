<?php

namespace AdventOfCode\Year2025;

/**
 * Day 11: Reactor
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
	 * Parsed data from the input file (graph adjacency list).
	 *
	 * @var array<string, array<string>>
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
	 * Part 1: Count all paths from "you" to "out"
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$cache = [];
		return $this->count_paths( 'you', $cache );
	}

	/**
	 * Part 2: Count paths from "svr" to "out" that visit both "dac" and "fft"
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$cache = [];
		return $this->count_paths_with_state( 'svr', false, false, $cache );
	}

	/**
	 * Counts the number of paths from a given node to "out" using memoization.
	 *
	 * @param string                $node  The current node.
	 * @param array<string, int>    $cache Memoization cache.
	 *
	 * @return int
	 */
	private function count_paths( string $node, array &$cache ): int {
		// If we've reached "out", there's exactly 1 path
		if ( $node === 'out' ) {
			return 1;
		}

		// Check cache
		if ( isset( $cache[ $node ] ) ) {
			return $cache[ $node ];
		}

		// If node doesn't exist in graph, return 0
		if ( ! isset( $this->data[ $node ] ) ) {
			$cache[ $node ] = 0;
			return 0;
		}

		// Sum paths from all neighbors
		$count = 0;
		foreach ( $this->data[ $node ] as $neighbor ) {
			$count += $this->count_paths( $neighbor, $cache );
		}

		// Cache and return
		$cache[ $node ] = $count;
		return $count;
	}

	/**
	 * Counts the number of paths from a given node to "out" that visit both "dac" and "fft"
	 *
	 * @param string                $node    The current node.
	 * @param bool                  $seen_dac Whether "dac" has been visited on the current path.
	 * @param bool                  $seen_fft Whether "fft" has been visited on the current path.
	 * @param array<string, int>    $cache   Memoization cache.
	 *
	 * @return int
	 */
	private function count_paths_with_state( string $node, bool $seen_dac, bool $seen_fft, array &$cache ): int {
		// If we've reached "out", return 1 if both required nodes were visited
		if ( $node === 'out' ) {
			return ($seen_dac && $seen_fft) ? 1 : 0;
		}

		// Create cache key from node and state
		$cache_key = $node . '|' . ($seen_dac ? '1' : '0') . '|' . ($seen_fft ? '1' : '0');

		// Check cache
		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		// If node doesn't exist, return 0
		if ( ! isset( $this->data[ $node ] ) ) {
			$cache[ $cache_key ] = 0;
			return 0;
		}

		// Update state flags based on current node
		$new_seen_dac = $seen_dac || ($node === 'dac');
		$new_seen_fft = $seen_fft || ($node === 'fft');

		// Sum paths from all neighbors with updated state
		$count = 0;
		foreach ( $this->data[ $node ] as $neighbor ) {
			$count += $this->count_paths_with_state( $neighbor, $new_seen_dac, $new_seen_fft, $cache );
		}

		// Cache and return
		$cache[ $cache_key ] = $count;
		return $count;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array<string, array<string>>
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-11-test.txt' : '/data/day-11.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$graph = [];
		foreach ( $lines as $line ) {
			$parts = explode( ': ', $line, 2 );
			if ( count( $parts ) === 2 ) {
				$node           = $parts[0];
				$neighbors      = explode( ' ', trim( $parts[1] ) );
				$graph[ $node ] = $neighbors;
			}
		}

		return $graph;
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
			'test' => 5,
			'real' => 683,
		],
		2 => [
			'test' => 2,
			'real' => 533996779677200,
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
