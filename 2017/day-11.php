<?php

namespace AdventOfCode\Year2017;

/**
 * Day 11: Hex Ed
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
	 * Part 1: Find fewest steps to reach the child process on a hex grid.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$moves = [
			'n'  => [ 0,  1, -1 ],
			's'  => [ 0, -1,  1 ],
			'ne' => [ 1,  0, -1 ],
			'sw' => [ -1, 0,  1 ],
			'nw' => [ -1, 1,  0 ],
			'se' => [ 1, -1,  0 ],
		];

		$x = $y = $z = 0;

		foreach ( $this->data as $dir ) {
			[ $dx, $dy, $dz ] = $moves[ $dir ];
			$x += $dx;
			$y += $dy;
			$z += $dz;
		}

		return ( abs( $x ) + abs( $y ) + abs( $z ) ) / 2;
	}

	/**
	 * Part 2: Find the furthest distance ever reached during the walk.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$moves = [
			'n'  => [ 0,  1, -1 ],
			's'  => [ 0, -1,  1 ],
			'ne' => [ 1,  0, -1 ],
			'sw' => [ -1, 0,  1 ],
			'nw' => [ -1, 1,  0 ],
			'se' => [ 1, -1,  0 ],
		];

		$x = $y = $z = 0;
		$max = 0;

		foreach ( $this->data as $dir ) {
			[ $dx, $dy, $dz ] = $moves[ $dir ];
			$x += $dx;
			$y += $dy;
			$z += $dz;

			$dist = ( abs( $x ) + abs( $y ) + abs( $z ) ) / 2;
			$max  = max( $max, $dist );
		}

		return $max;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-11-test.txt' : '/data/day-11.txt';

		return explode( ',', trim( file_get_contents( __DIR__ . $file ) ) );
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
			'test' => 3,
			'real' => 670,
		],
		2 => [
			'test' => 3,
			'real' => 1426,
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
