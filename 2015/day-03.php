<?php

namespace AdventOfCode\Year2015;

/**
 * Day 03: Perfectly Spherical Houses in a Vacuum
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

	private static array $moves = [
		'^' => [ 0, 1 ],
		'v' => [ 0, -1 ],
		'>' => [ 1, 0 ],
		'<' => [ -1, 0 ],
	];

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
	/**
	 * Part 1: Count houses that receive at least one present.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$x       = 0;
		$y       = 0;
		$visited = [ '0,0' => true ];

		foreach ( $this->data as $dir ) {
			[ $dx, $dy ] = self::$moves[ $dir ];
			$x += $dx;
			$y += $dy;
			$visited["$x,$y"] = true;
		}

		return count( $visited );
	}

	/**
	 * Part 2: Santa and Robo-Santa take turns moving.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$pos     = [ [ 0, 0 ], [ 0, 0 ] ];
		$visited = [ '0,0' => true ];

		foreach ( $this->data as $i => $dir ) {
			$who = $i % 2;
			[ $dx, $dy ] = self::$moves[ $dir ];
			$pos[ $who ][0] += $dx;
			$pos[ $who ][1] += $dy;
			$visited[ $pos[ $who ][0] . ',' . $pos[ $who ][1] ] = true;
		}

		return count( $visited );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-03-test.txt' : '/data/day-03.txt';

		return str_split( trim( file_get_contents( __DIR__ . $file ) ) );
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
			'test' => 4,
			'real' => 2081,
		],
		2 => [
			'test' => 3,
			'real' => 2341,
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
