<?php

namespace AdventOfCode\Year2020;

ini_set('memory_limit', '2048M');

/**
 * Day 15: Rambunctious Recitation
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
	 * Part 1: Take the starting numbers and determine the 2020th number spoken.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$numbers = $this->data;
		$spoken  = [];

		// Initialize the spoken numbers
		foreach ( $numbers as $i => $number ) {
			$spoken[ $number ] = $i + 1;
		}

		$last_number = end( $numbers );
		$turn        = count( $numbers ) + 1;

		while ( $turn <= 2020 ) {
			$number = 0;

			if ( isset( $spoken[ $last_number ] ) ) {
				$number = $turn - 1 - $spoken[ $last_number ];
			}

			$spoken[ $last_number ] = $turn - 1;
			$last_number            = $number;
			$turn++;
		}

		return $last_number;
	}

	/**
	 * Part 2: Take the starting numbers and determine the 30000000th number spoken.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$numbers     = $this->data;
		$size        = 30000000;
		$last_spoken = array_fill( 0, $size, 0 );
		$is_spoken   = array_fill( 0, $size, false );

		// Initialize the spoken numbers
		foreach ( $numbers as $i => $number ) {
			$last_spoken[ $number ] = $i + 1;
			$is_spoken[ $number ]   = true;
		}

		$last_number = end( $numbers );

		for ( $turn = count( $numbers ) + 1; $turn <= $size; $turn ++ ) {
			if ( ! $is_spoken[ $last_number ] ) {
				$next_number = 0;
			} else {
				$next_number = $turn - 1 - $last_spoken[ $last_number ];
			}

			$last_spoken[ $last_number ] = $turn - 1;
			$is_spoken[ $last_number ]   = true;
			$last_number                 = $next_number;
		}

		return $last_number;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-15-test.txt' : '/data/day-15.txt';

		return array_map( 'intval', explode( ',', file_get_contents( dirname( __FILE__ ) . $file ) ) );
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
			'test' => 436,
			'real' => 319,
		],
		2 => [
			'test' => 175594,
			'real' => 2424,
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
