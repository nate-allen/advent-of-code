<?php

namespace AdventOfCode\Year2015;

/**
 * Day 18: Like a GIF For Your Yard
 */
class Day18 {
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
	 * Part 1: Count lights on after 100 steps.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->simulate( false );
	}

	/**
	 * Part 2: Count lights on after 100 steps with corners stuck on.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->simulate( true );
	}

	/**
	 * Simulates the Game of Life grid for the given number of steps.
	 *
	 * @param bool $corners_stuck Whether corner lights are always on.
	 *
	 * @return integer
	 */
	private function simulate( bool $corners_stuck ): int {
		$grid  = $this->data;
		$size  = count( $grid );
		$steps = $this->is_test ? ( $corners_stuck ? 5 : 4 ) : 100;

		if ( $corners_stuck ) {
			$grid[0][0] = $grid[0][ $size - 1 ] = $grid[ $size - 1 ][0] = $grid[ $size - 1 ][ $size - 1 ] = '#';
		}

		for ( $s = 0; $s < $steps; $s++ ) {
			$new = $grid;

			for ( $r = 0; $r < $size; $r++ ) {
				for ( $c = 0; $c < $size; $c++ ) {
					$on = 0;

					for ( $dr = -1; $dr <= 1; $dr++ ) {
						for ( $dc = -1; $dc <= 1; $dc++ ) {
							if ( $dr === 0 && $dc === 0 ) {
								continue;
							}
							$nr = $r + $dr;
							$nc = $c + $dc;
							if ( $nr >= 0 && $nr < $size && $nc >= 0 && $nc < $size && $grid[ $nr ][ $nc ] === '#' ) {
								$on++;
							}
						}
					}

					if ( $grid[ $r ][ $c ] === '#' ) {
						$new[ $r ][ $c ] = ( $on === 2 || $on === 3 ) ? '#' : '.';
					} else {
						$new[ $r ][ $c ] = ( $on === 3 ) ? '#' : '.';
					}
				}
			}

			$grid = $new;

			if ( $corners_stuck ) {
				$grid[0][0] = $grid[0][ $size - 1 ] = $grid[ $size - 1 ][0] = $grid[ $size - 1 ][ $size - 1 ] = '#';
			}
		}

		$count = 0;
		foreach ( $grid as $row ) {
			$count += count( array_filter( $row, fn( $c ) => $c === '#' ) );
		}

		return $count;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-18-test.txt' : '/data/day-18.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( 'str_split', $lines );
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
	$day18  = new Day18( $test, $part );
	$result = $day18->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 4,
			'real' => 814,
		],
		2 => [
			'test' => 17,
			'real' => 924,
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
