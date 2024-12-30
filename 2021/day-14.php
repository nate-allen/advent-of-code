<?php

namespace AdventOfCode\Year2021;

/**
 * Day 14: TITLE HERE
 */
class Day14 {
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
			1 => $this->solve( 10 ),
			2 => $this->solve( 40 ),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Simulates the polymerization pair insertion process. Calculates the difference between the most and least common
	 * elements after a certain number of insertion steps.
	 *
	 * @param int $steps The number of steps to simulate.
	 *
	 * @return int
	 */
	private function solve( int $steps ): int {
		[ $template, $rules ] = $this->data;

		// Initialize pair counts from the template
		$pair_counts = [];
		for ( $i = 0; $i < strlen( $template ) - 1; $i ++ ) {
			$pair                 = $template[ $i ] . $template[ $i + 1 ];
			$pair_counts[ $pair ] = ( $pair_counts[ $pair ] ?? 0 ) + 1;
		}

		// Initialize character counts (e.g. 'NNCB' becomes [['NN'] => 1, ['NC'] => 1, ['CB'] => 1])
		$char_counts = [];
		foreach ( str_split( $template ) as $char ) {
			$char_counts[ $char ] = ( $char_counts[ $char ] ?? 0 ) + 1;
		}

		// Apply the pair insertion rules for the specified number of steps
		for ( $step = 0; $step < $steps; $step ++ ) {
			$new_pair_counts = [];
			foreach ( $pair_counts as $pair => $count ) {
				if ( isset( $rules[ $pair ] ) ) {
					$insert = $rules[ $pair ];

					// Update new pairs created by the rule
					$left_pair  = $pair[0] . $insert;
					$right_pair = $insert . $pair[1];

					$new_pair_counts[ $left_pair ]  = ( $new_pair_counts[ $left_pair ] ?? 0 ) + $count;
					$new_pair_counts[ $right_pair ] = ( $new_pair_counts[ $right_pair ] ?? 0 ) + $count;

					// Update character counts for the inserted character
					$char_counts[ $insert ] = ( $char_counts[ $insert ] ?? 0 ) + $count;
				}
			}

			$pair_counts = $new_pair_counts;
		}

		// Subtract the most and least common elements
		return max( $char_counts ) - min( $char_counts );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return void
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-14-test.txt' : '/data/day-14.txt';
		[ $template, $rules ] = explode( "\n\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$rules = array_reduce(
			explode( "\n", $rules ),
			function ( $carry, $line ) {
				[ $pair, $insert ] = explode( " -> ", $line );
				$carry[ $pair ] = $insert;

				return $carry;
			},
			[]
		);

		return [ $template, $rules ];
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day14  = new Day14( $test, $part );
	$result = $day14->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 1588,
			'real' => 2657,
		],
		2 => [
			'test' => 2188189693529,
			'real' => 2911561572630,
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
