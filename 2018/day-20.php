<?php

namespace AdventOfCode\Year2018;

/**
 * Day 20: A Regular Map
 */
class Day20 {
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
	 * @var string
	 */
	private string $data;

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
	 * Part 1: Find the room furthest from start (maximum shortest path distance).
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$distances = $this->build_distance_map();
		return max( $distances );
	}

	/**
	 * Part 2: Count rooms at least 1000 doors away.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$distances = $this->build_distance_map();
		return count( array_filter( $distances, fn( $d ) => $d >= 1000 ) );
	}

	/**
	 * Builds a map of room positions to their minimum distance from start.
	 *
	 * @return array Map of "x,y" => distance
	 */
	private function build_distance_map(): array {
		// Map of room positions to minimum distance from start
		$distances = [ '0,0' => 0 ];

		// Current position and distance
		$x    = 0;
		$y    = 0;
		$dist = 0;

		// Stack for handling branches (parentheses)
		$stack = [];

		// Direction mappings: [dx, dy]
		$directions = [
			'N' => [ 0, -1 ],
			'S' => [ 0, 1 ],
			'E' => [ 1, 0 ],
			'W' => [ -1, 0 ],
		];

		// Parse the regex character by character
		$length = strlen( $this->data );
		for ( $i = 0; $i < $length; $i++ ) {
			$char = $this->data[ $i ];

			if ( $char === '(' ) {
				// Start of branch: save current state
				$stack[] = [ $dist, $x, $y ];
			} elseif ( $char === ')' ) {
				// End of branch: restore to branch point
				if ( ! empty( $stack ) ) {
					list( $dist, $x, $y ) = array_pop( $stack );
				}
			} elseif ( $char === '|' ) {
				// Alternative in branch: reset to branch point (peek, don't pop)
				if ( ! empty( $stack ) ) {
					list( $dist, $x, $y ) = $stack[ count( $stack ) - 1 ];
				}
			} elseif ( isset( $directions[ $char ] ) ) {
				// Direction: move and update distance
				list( $dx, $dy ) = $directions[ $char ];
				$x              += $dx;
				$y              += $dy;
				$dist           += 1;

				// Update minimum distance to this room
				$key = "$x,$y";
				if ( ! isset( $distances[ $key ] ) || $dist < $distances[ $key ] ) {
					$distances[ $key ] = $dist;
				}
			}
		}

		return $distances;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return string The regex pattern (without ^ and $)
	 */
	private function parse_data( bool $test ): string {
		$file  = $test ? '/data/day-20-test.txt' : '/data/day-20.txt';
		$regex = trim( file_get_contents( __DIR__ . $file ) );

		// Remove ^ at start and $ at end
		if ( str_starts_with( $regex, '^' ) ) {
			$regex = substr( $regex, 1 );
		}
		if ( str_ends_with( $regex, '$' ) ) {
			$regex = substr( $regex, 0, -1 );
		}

		return $regex;
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
	$day20  = new Day20( $test, $part );
	$result = $day20->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 3,
			'real' => 3721,
		],
		2 => [
			'test' => 0,
			'real' => 8613,
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
