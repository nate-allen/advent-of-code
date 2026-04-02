<?php

namespace AdventOfCode\Year2015;

/**
 * Day 17: No Such Thing as Too Much
 */
class Day17 {
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
	 * Part 1: Count combinations that exactly fill the target.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$combos = $this->find_combinations();

		return count( $combos );
	}

	/**
	 * Part 2: Count combinations using the minimum number of containers.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$combos    = $this->find_combinations();
		$min_count = min( $combos );

		return count( array_filter( $combos, fn( $c ) => $c === $min_count ) );
	}

	/**
	 * Finds all combinations of containers that sum to the target.
	 *
	 * @return array Array of container counts for each valid combination.
	 */
	private function find_combinations(): array {
		$target = $this->is_test ? 25 : 150;
		$combos = [];
		$n      = count( $this->data );

		for ( $mask = 1; $mask < ( 1 << $n ); $mask++ ) {
			$sum   = 0;
			$count = 0;

			for ( $i = 0; $i < $n; $i++ ) {
				if ( $mask & ( 1 << $i ) ) {
					$sum += $this->data[ $i ];
					$count++;
				}
			}

			if ( $sum === $target ) {
				$combos[] = $count;
			}
		}

		return $combos;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-17-test.txt' : '/data/day-17.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( 'intval', $lines );
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
	$day17  = new Day17( $test, $part );
	$result = $day17->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 4,
			'real' => 4372,
		],
		2 => [
			'test' => 3,
			'real' => 4,
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
