<?php

namespace AdventOfCode\Year2024;

/**
 * Day 07: Bridge Repair
 */
class Day07 {
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
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Determine total calibration result by trying addition and multiplication.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$total = 0;

		foreach ( $this->data as $line ) {
			[ $target, $numbers ] = $line;
			if ( $this->do_math( $numbers, $target ) ) {
				$total += $target;
			}
		}

		return $total;
	}

	/**
	 * Part 2: Determine total calibration result by trying addition, multiplication, and concatenation.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$total = 0;

		foreach ( $this->data as $line ) {
			[ $target, $numbers ] = $line;
			if ( $this->do_math( $numbers, $target ) ) {
				$total += $target;
			}
		}

		return $total;
	}

	/**
	 * Recursively evaluates all combinations of addition, multiplication,
	 * and (for part 2) concatenation to determine if the target value can be produced.
	 *
	 * @param array        $numbers The remaining numbers to process.
	 * @param integer      $target  The target value to match.
	 * @param integer|null $current The current value of the ongoing calculation.
	 *
	 * @return bool
	 */
	private function do_math(array $numbers, int $target, ?int $current = null): bool {
		// If this is the first iteration, get the first number.
		if ( $current === null ) {
			$current = array_shift( $numbers );
		}

		// Optimization: If the current value is already greater than the target, it's impossible to reach the target.
		// Bail out early to save time.
		if ( $current > $target ) {
			return false;
		}

		// When $numbers is finally empty we can check if if $current matches the target.
		if ( empty( $numbers ) ) {
			return $current === $target;
		}

		// Get the next number.
		$next = array_shift( $numbers );

		// Try addition
		if ( $this->do_math( $numbers, $target, $current + $next ) ) {
			return true;
		}

		// Try multiplication
		if ( $this->do_math( $numbers, $target, $current * $next ) ) {
			return true;
		}

		// Try concatenation (only for part 2)
		if ( $this->part === 2 && $this->do_math( $numbers, $target, (int) ( $current . $next ) ) ) {
			return true;
		}

		// If none of the operations match the target, return false
		return false;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-07-test.txt' : '/data/day-07.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( function ( $line ) {
			[ $target, $numbers ] = explode( ':', $line );
			$numbers = array_map( 'intval', explode( ' ', trim( $numbers ) ) );

			return [ (int) $target, $numbers ];
		}, $lines );
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
	$day07  = new Day07( $test, $part );
	$result = $day07->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 3749,
			'real' => 945512582195,
		],
		2 => [
			'test' => 11387,
			'real' => 0,
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
