<?php

namespace AdventOfCode\Year2016;

/**
 * Day 15: Timing is Everything
 */
class Day15 {
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
	 * Parsed disc data: array of [positions, start_position].
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
	 * Part 1: First time to press the button to get a capsule through.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->find_time( $this->data );
	}

	/**
	 * Part 2: Add a new disc with 11 positions starting at 0.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$discs   = $this->data;
		$discs[] = [ 11, 0 ];

		return $this->find_time( $discs );
	}

	/**
	 * Finds the first time to press the button so the capsule passes all discs.
	 *
	 * At time t, disc i (1-indexed) must be at position 0 at time t + i.
	 * So: (start_position_i + t + i) % positions_i === 0 for all discs.
	 *
	 * @param array $discs Array of [positions, start_position].
	 *
	 * @return integer
	 */
	private function find_time( array $discs ): int {
		for ( $t = 0; ; $t++ ) {
			$valid = true;

			foreach ( $discs as $i => $disc ) {
				if ( ( $disc[1] + $t + $i + 1 ) % $disc[0] !== 0 ) {
					$valid = false;
					break;
				}
			}

			if ( $valid ) {
				return $t;
			}
		}
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of [positions, start_position].
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-15-test.txt' : '/data/day-15.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$discs = [];

		foreach ( $lines as $line ) {
			preg_match( '/Disc #\d+ has (\d+) positions; at time=0, it is at position (\d+)\./', $line, $m );
			$discs[] = [ (int) $m[1], (int) $m[2] ];
		}

		return $discs;
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
	$day15  = new Day15( $test, $part );
	$result = $day15->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 5,
			'real' => 400589,
		],
		2 => [
			'test' => 0,
			'real' => 3045959,
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
