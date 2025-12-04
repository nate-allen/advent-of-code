<?php

namespace AdventOfCode\Year2025;

/**
 * Day 04: Printing Department
 */
class Day04 {
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
	 * Part 1: Count paper rolls accessible by forklifts (fewer than 4 adjacent rolls)
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return count( $this->find_accessible_rolls() );
	}

	/**
	 * Part 2: Count total rolls removed by iterative forklift access
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$total_removed = 0;

		while ( true ) {
			$accessible = $this->find_accessible_rolls();

			if ( count( $accessible ) === 0 ) {
				break;  // No more rolls can be removed
			}

			// Remove all accessible rolls
			foreach ( $accessible as list( $row, $col ) ) {
				$this->data[ $row ][ $col ] = '.';
			}

			$total_removed += count( $accessible );
		}

		return $total_removed;
	}

	/**
	 * Finds all accessible paper rolls (those with fewer than 4 adjacent rolls).
	 *
	 * @return array Array of [row, col] coordinates of accessible rolls.
	 */
	private function find_accessible_rolls(): array {
		$accessible = [];
		$rows       = count( $this->data );
		$cols       = count( $this->data[0] );

		for ( $row = 0; $row < $rows; $row++ ) {
			for ( $col = 0; $col < $cols; $col++ ) {
				if ( $this->data[ $row ][ $col ] === '@' ) {
					$adjacent_count = $this->count_adjacent_rolls( $row, $col );
					if ( $adjacent_count < 4 ) {
						$accessible[] = [ $row, $col ];
					}
				}
			}
		}

		return $accessible;
	}

	/**
	 * Counts adjacent paper rolls (@) in all 8 directions from given position.
	 *
	 * @param int $row The row index.
	 * @param int $col The column index.
	 *
	 * @return int The count of adjacent paper rolls.
	 */
	private function count_adjacent_rolls( int $row, int $col ): int {
		$count      = 0;
		$rows       = count( $this->data );
		$cols       = count( $this->data[0] );
		$directions = [
			[ -1, -1 ], // top-left
			[ -1, 0 ],  // top
			[ -1, 1 ],  // top-right
			[ 0, -1 ],  // left
			[ 0, 1 ],   // right
			[ 1, -1 ],  // bottom-left
			[ 1, 0 ],   // bottom
			[ 1, 1 ],   // bottom-right
		];

		foreach ( $directions as $dir ) {
			$new_row = $row + $dir[0];
			$new_col = $col + $dir[1];

			// Check boundaries
			if ( $new_row >= 0 && $new_row < $rows && $new_col >= 0 && $new_col < $cols ) {
				if ( $this->data[ $new_row ][ $new_col ] === '@' ) {
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-04-test.txt' : '/data/day-04.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$grid = [];
		foreach ( $lines as $line ) {
			$grid[] = str_split( $line );
		}

		return $grid;
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
	$day04  = new Day04( $test, $part );
	$result = $day04->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 13,
			'real' => 1564,
		],
		2 => [
			'test' => 43,
			'real' => 9401,
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
