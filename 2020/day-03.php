<?php

namespace AdventOfCode\Year2020;

/**
 * Day 03: Toboggan Trajectory
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
	 * Part 1: Starting at the top-left corner of your map and following a slope of right 3 and down 1, how many trees would you encounter?
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$width  = strlen( $this->data[0] );
		$height = count( $this->data );
		$x      = 0;
		$y      = 0;
		$trees  = 0;

		while ( $y < $height ) {
			if ( $this->data[ $y ][ $x ] === '#' ) {
				$trees++;
			}

			$x = ( $x + 3 ) % $width;
			$y++;
		}

		return $trees;
	}

	/**
	 * Part 2: Determine the number of trees you would encounter if you start at the top-left corner and traverse the
	 *         map all the way to the bottom for each of the following slopes:
	 *
	 * 	   - Right 1, down 1.
	 * 	   - Right 3, down 1.
	 * 	   - Right 5, down 1.
	 * 	   - Right 7, down 1.
	 * 	   - Right 1, down 2.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$width  = strlen( $this->data[0] );
		$height = count( $this->data );
		$slopes = [
			[ 1, 1 ],
			[ 3, 1 ],
			[ 5, 1 ],
			[ 7, 1 ],
			[ 1, 2 ],
		];
		$trees  = [];

		foreach ( $slopes as $slope ) {
			$x     = 0;
			$y     = 0;
			$trees[] = 0;

			while ( $y < $height ) {
				if ( $this->data[ $y ][ $x ] === '#' ) {
					$trees[ count( $trees ) - 1 ]++;
				}

				$x = ( $x + $slope[0] ) % $width;
				$y += $slope[1];
			}
		}

		return array_product( $trees );
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
			'test' => 7,
			'real' => 214,
		],
		2 => [
			'test' => 336,
			'real' => 8336352024,
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
