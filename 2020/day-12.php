<?php

namespace AdventOfCode\Year2020;

/**
 * Day 12: Rain Risk
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

	/**
	 * The directions in which the ship can move.
	 *
	 * @var array
	 */
	private array $directions = [ 'N', 'E', 'S', 'W' ];

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
	 * Part 1: Figure out where the navigation instructions lead. What is the Manhattan distance between that location
	 *         and the ship's starting position?
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$directions = $this->directions;
		$direction  = 'E';

		$x = $y = 0;

		foreach ( $this->data as $line ) {
			$instruction = $line[0];
			$value       = (int) substr( $line, 1 );

			if ( 'F' === $instruction ) {
				$instruction = $direction;
			}

			if ( 'N' === $instruction ) {
				$y += $value;
			} elseif ( 'S' === $instruction ) {
				$y -= $value;
			} elseif ( 'E' === $instruction ) {
				$x += $value;
			} elseif ( 'W' === $instruction ) {
				$x -= $value;
			} elseif ( 'L' === $instruction ) {
				$direction = $directions[ ( 4 + array_search( $direction, $directions ) - ( $value / 90 ) ) % 4 ];
			} elseif ( 'R' === $instruction ) {
				$direction = $directions[ ( array_search( $direction, $directions ) + ( $value / 90 ) ) % 4 ];
			}
		}

		return abs( $x ) + abs( $y );
	}

	/**
	 * Part 2: Figure out where the navigation instructions actually lead. What is the Manhattan distance between that
	 * 	       location and the ship's starting position?
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$x = $y = 0;
		$wx = 10;
		$wy = 1;

		foreach ( $this->data as $line ) {
			$instruction = $line[0];
			$value       = (int) substr( $line, 1 );

			if ( 'F' === $instruction ) {
				$x += $wx * $value;
				$y += $wy * $value;
			} elseif ( 'N' === $instruction ) {
				$wy += $value;
			} elseif ( 'S' === $instruction ) {
				$wy -= $value;
			} elseif ( 'E' === $instruction ) {
				$wx += $value;
			} elseif ( 'W' === $instruction ) {
				$wx -= $value;
			} elseif ( 'L' === $instruction ) {
				for ( $i = 0; $i < $value / 90; $i++ ) {
					$temp = $wx;
					$wx   = -$wy;
					$wy   = $temp;
				}
			} elseif ( 'R' === $instruction ) {
				for ( $i = 0; $i < $value / 90; $i++ ) {
					$temp = $wx;
					$wx   = $wy;
					$wy   = -$temp;
				}
			}
		}

		return abs( $x ) + abs( $y );
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

		return explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
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
			'test' => 25,
			'real' => 521,
		],
		2 => [
			'test' => 286,
			'real' => 22848,
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
