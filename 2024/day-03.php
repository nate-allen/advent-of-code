<?php

namespace AdventOfCode\Year2024;

/**
 * Day 03: Mull It Over
 */
class Day03 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var int
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
	 * @return int
	 */
	public function run(): int {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Add up all the results of the multiplication instructions.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		preg_match_all( '/mul\(([0-9]{1,3}),([0-9]{1,3})\)/', $this->data, $matches, PREG_SET_ORDER );

		$total = 0;

		foreach ( $matches as $match ) {
			$total += intval( $match[1] ) * intval( $match[2] );
		}

		return $total;
	}

	/**
	 * Part 2: Add up all the results of just the *enabled* multiplication instructions.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$pattern = '/
			(?P<mul>mul\s*\(\s*(?P<x>[0-9]{1,3})\s*,\s*(?P<y>[0-9]{1,3})\s*\)) |
			(?P<do>do\s*\(\s*\)) |
			(?P<dont>don\'t\s*\(\s*\))
		/x';

		preg_match_all( $pattern, $this->data, $matches, PREG_SET_ORDER );

		$total   = 0;
		$enabled = true;

		foreach ( $matches as $match ) {
			if ( ! empty( $match['do'] ) ) {
				$enabled = true;
			} elseif ( ! empty( $match['dont'] ) ) {
				$enabled = false;
			} elseif ( ! empty( $match['mul'] ) && $enabled ) {
				$total += intval( $match['x'] ) * intval( $match['y'] );
			}
		}

		return $total;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return string
	 */
	private function parse_data( bool $test ): string {
		$file = $test ? '/data/day-03-test.txt' : '/data/day-03.txt';

		return trim( file_get_contents( __DIR__ . $file ) );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param int $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day03  = new Day03( $test, $part );
	$result = $day03->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 161,
			'real' => 173785482
		],
		2 => [
			'test' => 48,
			'real' => 83158140
		],
	];

	printf( PHP_EOL );
	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $test ? $expected_values[ $part ]['test'] : $expected_values[ $part ]['real'] );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
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
