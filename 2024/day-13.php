<?php

namespace AdventOfCode\Year2024;

/**
 * Day 13: Claw Contraption
 */
class Day13 {
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
	 * @return int
	 */
	public function run(): int {
		return match ( $this->part ) {
			1, 2 => $this->find_tokens(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Uses linear algebra to find the number of tokens it takes to get a prize.
	 *
	 * @return int
	 */
	private function find_tokens(): int {
		$total_tokens = 0;

		foreach ( $this->data as $machine ) {
			if ( 2 === $this->part ) {
				$machine['P']['X'] += 10000000000000;
				$machine['P']['Y'] += 10000000000000;
			}

			$a_x = $machine['A']['X'];
			$a_y = $machine['A']['Y'];
			$b_x = $machine['B']['X'];
			$b_y = $machine['B']['Y'];
			$p_x = $machine['P']['X'];
			$p_y = $machine['P']['Y'];

			$move_x = ( $b_y * $p_x - $b_x * $p_y ) / ( $b_y * $a_x - $b_x * $a_y );
			$move_y = ( $a_x * $b_x * $p_y - $p_x * $b_x * $a_y ) / ( $b_x * ( $b_y * $a_x - $b_x * $a_y ) );

			if ( is_int( $move_x ) && is_int( $move_y ) && $move_x >= 0 && $move_y >= 0 ) {
				$total_tokens += 3 * $move_x + $move_y;
			}
		}

		return $total_tokens;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-13-test.txt' : '/data/day-13.txt';
		$lines = explode( PHP_EOL . PHP_EOL, trim( file_get_contents( __DIR__ . $file ) ) );

		$data = [];
		foreach ( $lines as $group ) {
			$rows = explode( PHP_EOL, $group );
			preg_match( '/X\+([\d]+), Y\+([\d]+)/', $rows[0], $matchesA );
			preg_match( '/X\+([\d]+), Y\+([\d]+)/', $rows[1], $matchesB );
			preg_match( '/X=([\d]+), Y=([\d]+)/', $rows[2], $matchesP );

			$data[] = [
				'A' => [ 'X' => intval( $matchesA[1] ), 'Y' => intval( $matchesA[2] ) ],
				'B' => [ 'X' => intval( $matchesB[1] ), 'Y' => intval( $matchesB[2] ) ],
				'P' => [ 'X' => intval( $matchesP[1] ), 'Y' => intval( $matchesP[2] ) ],
			];
		}

		return $data;
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
	$day13  = new Day13( $test, $part );
	$result = $day13->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 480,
			'real' => 36758,
		],
		2 => [
			'test' => 875318608908,
			'real' => 76358113886726,
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
