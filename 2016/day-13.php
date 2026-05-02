<?php

namespace AdventOfCode\Year2016;

/**
 * Day 13: A Maze of Twisty Little Cubicles
 */
class Day13 {
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
	 * The office designer's favorite number.
	 *
	 * @var int
	 */
	private int $data;

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
	 * Part 1: Fewest steps to reach the target coordinate.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$target = $this->is_test ? [ 7, 4 ] : [ 31, 39 ];

		return $this->bfs( $target );
	}

	/**
	 * Part 2: Count distinct locations reachable in at most 50 steps.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$queue   = [ [ 1, 1, 0 ] ];
		$visited = [ '1,1' => true ];
		$head    = 0;
		$dirs    = [ [ 0, 1 ], [ 0, -1 ], [ 1, 0 ], [ -1, 0 ] ];

		while ( $head < count( $queue ) ) {
			[ $x, $y, $steps ] = $queue[ $head++ ];

			if ( $steps >= 50 ) {
				continue;
			}

			foreach ( $dirs as [ $dx, $dy ] ) {
				$nx  = $x + $dx;
				$ny  = $y + $dy;
				$key = "$nx,$ny";

				if ( $nx < 0 || $ny < 0 || isset( $visited[ $key ] ) || $this->is_wall( $nx, $ny ) ) {
					continue;
				}

				$visited[ $key ] = true;
				$queue[]         = [ $nx, $ny, $steps + 1 ];
			}
		}

		return count( $visited );
	}

	/**
	 * BFS from (1,1) to the target coordinate.
	 *
	 * @param array $target [x, y] target coordinate.
	 *
	 * @return integer Minimum steps to reach target.
	 */
	private function bfs( array $target ): int {
		$queue   = [ [ 1, 1, 0 ] ];
		$visited = [ '1,1' => true ];
		$head    = 0;
		$dirs    = [ [ 0, 1 ], [ 0, -1 ], [ 1, 0 ], [ -1, 0 ] ];

		while ( $head < count( $queue ) ) {
			[ $x, $y, $steps ] = $queue[ $head++ ];

			if ( $x === $target[0] && $y === $target[1] ) {
				return $steps;
			}

			foreach ( $dirs as [ $dx, $dy ] ) {
				$nx = $x + $dx;
				$ny = $y + $dy;

				if ( $nx < 0 || $ny < 0 ) {
					continue;
				}

				$key = "$nx,$ny";

				if ( isset( $visited[ $key ] ) ) {
					continue;
				}

				if ( $this->is_wall( $nx, $ny ) ) {
					continue;
				}

				$visited[ $key ] = true;
				$queue[]         = [ $nx, $ny, $steps + 1 ];
			}
		}

		return -1;
	}

	/**
	 * Determines if a coordinate is a wall.
	 *
	 * @param int $x X coordinate.
	 * @param int $y Y coordinate.
	 *
	 * @return bool
	 */
	private function is_wall( int $x, int $y ): bool {
		$val = $x * $x + 3 * $x + 2 * $x * $y + $y + $y * $y + $this->data;

		return substr_count( decbin( $val ), '1' ) % 2 === 1;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return int The designer's favorite number.
	 */
	private function parse_data( bool $test ): int {
		$file = $test ? '/data/day-13-test.txt' : '/data/day-13.txt';

		return (int) trim( file_get_contents( __DIR__ . $file ) );
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
	$day13  = new Day13( $test, $part );
	$result = $day13->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 11,
			'real' => 86,
		],
		2 => [
			'test' => 0,
			'real' => 127,
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
