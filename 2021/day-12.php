<?php

namespace AdventOfCode\Year2021;

/**
 * Day 12: Passage Pathing
 */
class Day12 {
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
	 * Part 1: Finds the number of distinct paths through the cave system that start at 'start', end at 'end', and visit
	 *         small caves (lowercase) at most once.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		// Find all paths from 'start' to 'end'
		$paths = $this->find_paths( 'start', [], $this->data );

		// Return the number of paths found.
		return count( $paths );
	}

	/**
	 * Part 2: Finds the number of distinct paths through the cave system that start at 'start', end at 'end', and visit
	 *         one small cave (lowercase) up to two times.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		// Find all paths from 'start' to 'end'
		$paths = $this->find_more_paths('start', [], $this->data, false);

		// Return the number of paths found.
		return count($paths);
	}

	/**
	 * Recursively explores all paths through the cave that start at the given point and ends at 'end'.
	 *
	 * @param string $current The current cave being visited.
	 * @param array  $visited An associative array tracking visited small caves.
	 * @param array  $caves   The cave system.
	 * @param array  $path    The current path being explored (for tracking purposes).
	 *
	 * @return array
	 */
	private function find_paths(string $current, array $visited, array $caves, array $path = []): array {
		$path[] = $current;

		// If we reached the end, count this path.
		if ( $current === 'end' ) {
			return [ implode( ',', $path ) ];
		}

		// Mark small caves as visited.
		if ( ctype_lower( $current ) ) {
			$visited[ $current ] = true;
		}

		$paths = [];
		foreach ( $caves[ $current ] as $neighbor ) {
			// Skip if the neighbor is a small cave that has been visited
			if ( isset( $visited[ $neighbor ] ) && $visited[ $neighbor ] ) {
				continue;
			}

			// Recurse with updated state
			$paths = array_merge( $paths, $this->find_paths( $neighbor, $visited, $caves, $path ) );
		}

		return $paths;
	}

	/**
	 * Recursively explores all paths through the cave that start at the given point and ends at 'end',
	 * allowing one small cave to be visited twice.
	 *
	 * @param string $current       The current cave being visited.
	 * @param array  $visited       An associative array tracking visited small caves.
	 * @param array  $caves         The cave system.
	 * @param bool   $visited_twice Whether a small cave has already been visited twice.
	 * @param array  $path          The current path being explored (for tracking purposes).
	 *
	 * @return array
	 */
	private function find_more_paths( string $current, array $visited, array $caves, bool $visited_twice, array $path = [] ): array {
		$path[] = $current;

		// If we reached the end, count this path.
		if ( $current === 'end' ) {
			return [ implode( ',', $path ) ];
		}

		// Mark small caves as visited.
		if ( ctype_lower( $current ) ) {
			$visited[ $current ] = ( $visited[ $current ] ?? 0 ) + 1;
		}

		$paths = [];
		foreach ( $caves[ $current ] as $neighbor ) {
			if ( $neighbor === 'start' ) {
				// Skip revisiting the start cave.
				continue;
			}

			$is_small_cave = ctype_lower( $neighbor );
			$has_visited   = isset( $visited[ $neighbor ] ) && $visited[ $neighbor ] > 0;

			if ( $is_small_cave && $has_visited ) {
				// If visiting this small cave would exceed one revisit, skip it.
				if ( $visited_twice ) {
					continue;
				}

				// Allow revisiting this small cave if it hasn't been visited twice yet.
				$paths = array_merge( $paths, $this->find_more_paths( $neighbor, $visited, $caves, true, $path ) );
			} else {
				// Recurse with updated state.
				$paths = array_merge( $paths, $this->find_more_paths( $neighbor, $visited, $caves, $visited_twice, $path ) );
			}
		}

		return $paths;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-12-test.txt' : '/data/day-12.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$data = [];

		foreach ( $lines as $line ) {
			[ $from, $to ] = explode( '-', $line );
			$data[ $from ][] = $to;
			$data[ $to ][]   = $from;
		}

		return $data;
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
	$day12  = new Day12( $test, $part );
	$result = $day12->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 226,
			'real' => 4167,
		],
		2 => [
			'test' => 3509,
			'real' => 98441,
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
