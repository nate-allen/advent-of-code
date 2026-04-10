<?php

namespace AdventOfCode\Year2015;

/**
 * Day 24: It Hangs in the Balance
 */
class Day24 {
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
	 * Part 1: Find QE of ideal first group splitting packages into 3 groups.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->find_min_qe( 3 );
	}

	/**
	 * Part 2: Find QE of ideal first group splitting packages into 4 groups.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->find_min_qe( 4 );
	}

	/**
	 * Finds the minimum quantum entanglement for the first group.
	 *
	 * @param int $groups Number of groups to split into.
	 *
	 * @return integer
	 */
	private function find_min_qe( int $groups ): int {
		$target  = array_sum( $this->data ) / $groups;
		$best_qe = PHP_INT_MAX;

		// Try increasing group sizes until we find valid combinations.
		for ( $size = 1; $size <= count( $this->data ); $size++ ) {
			$this->find_combos( $this->data, $target, $size, 0, [], $best_qe );

			if ( $best_qe < PHP_INT_MAX ) {
				break;
			}
		}

		return $best_qe;
	}

	/**
	 * Recursively finds combinations of the given size that sum to target.
	 *
	 * @param array   $packages Available packages.
	 * @param int     $target   Target sum.
	 * @param int     $size     Required group size.
	 * @param int     $start    Starting index.
	 * @param array   $current  Current combination.
	 * @param int     &$best_qe Best quantum entanglement found.
	 */
	private function find_combos( array $packages, int $target, int $size, int $start, array $current, int &$best_qe ): void {
		if ( count( $current ) === $size ) {
			if ( $target === 0 ) {
				$qe      = array_product( $current );
				$best_qe = min( $best_qe, (int) $qe );
			}
			return;
		}

		for ( $i = $start; $i < count( $packages ); $i++ ) {
			if ( $packages[ $i ] > $target ) {
				continue;
			}

			$current[] = $packages[ $i ];
			$this->find_combos( $packages, $target - $packages[ $i ], $size, $i + 1, $current, $best_qe );
			array_pop( $current );
		}
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-24-test.txt' : '/data/day-24.txt';
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
	$day24  = new Day24( $test, $part );
	$result = $day24->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 99,
			'real' => 10723906903,
		],
		2 => [
			'test' => 44,
			'real' => 74850409,
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
