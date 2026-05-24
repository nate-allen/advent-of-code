<?php

namespace AdventOfCode\Year2017;

/**
 * Day 03: TITLE HERE
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
	 * Part 1: Find Manhattan distance from input square to square 1.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$target = (int) $this->data[0];

		if ( $target === 1 ) {
			return 0;
		}

		// Find which ring the target is on.
		$ring = 0;
		while ( ( 2 * $ring + 1 ) ** 2 < $target ) {
			$ring++;
		}

		// Side length of this ring is 2 * ring.
		$side_length = 2 * $ring;

		// Position along the ring (0-indexed from the first square of the ring).
		$pos = $target - ( ( 2 * ( $ring - 1 ) + 1 ) ** 2 + 1 );

		// Distance from the midpoint of the nearest side.
		$offset = $pos % $side_length;
		$distance_from_mid = abs( $offset - ( $side_length / 2 - 1 ) );

		return $ring + (int) $distance_from_mid;
	}

	/**
	 * Part 2: Find first value in stress test spiral larger than input.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$target = (int) $this->data[0];
		$grid   = [];
		$grid['0,0'] = 1;

		$x = 0;
		$y = 0;

		// Direction order: right, up, left, down.
		$dirs = [ [ 1, 0 ], [ 0, -1 ], [ -1, 0 ], [ 0, 1 ] ];
		$dir  = 0;
		$steps = 1;

		while ( true ) {
			for ( $s = 0; $s < 2; $s++ ) {
				for ( $i = 0; $i < $steps; $i++ ) {
					$x += $dirs[ $dir ][0];
					$y += $dirs[ $dir ][1];

					$sum = 0;
					for ( $dx = -1; $dx <= 1; $dx++ ) {
						for ( $dy = -1; $dy <= 1; $dy++ ) {
							$key = ( $x + $dx ) . ',' . ( $y + $dy );
							$sum += $grid[ $key ] ?? 0;
						}
					}

					$grid["$x,$y"] = $sum;

					if ( $sum > $target ) {
						return $sum;
					}
				}
				$dir = ( $dir + 1 ) % 4;
			}
			$steps++;
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
		$file  = $test ? '/data/day-03-test.txt' : '/data/day-03.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return $lines;
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
			'test' => 31,
			'real' => 430,
		],
		2 => [
			'test' => 1968,
			'real' => 312453,
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
