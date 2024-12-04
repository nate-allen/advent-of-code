<?php

namespace AdventOfCode\Year2024;

/**
 * Day 04: Ceres Search
 */
class Day04 {
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
	 * Part 1: Count all occurrences of the word "XMAS" in the all directions.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$rows  = count( $this->data );
		$cols  = strlen( $this->data[0] );
		$count = 0;

		$directions = [
			[ 0, 1 ],  // Horizontal right
			[ 1, 0 ],  // Vertical down
			[ 1, 1 ],  // Diagonal right
			[ 1, -1 ], // Diagonal left
		];

		for ( $r = 0; $r < $rows; $r ++ ) {
			for ( $c = 0; $c < $cols; $c ++ ) {
				foreach ( $directions as [$x, $y] ) {
					// Check forward
					if ( $this->matches_word( 'XMAS', $r, $c, $x, $y ) ) {
						$count ++;
					}
					// Also check it backward
					if ( $this->matches_word( 'SAMX', $r, $c, $x, $y ) ) {
						$count ++;
					}
				}
			}
		}

		return $count;
	}

	/**
	 * Part 2: Count all occurrences of two "MAS" patterns in an "X" shape.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$pattern = [
			[ 'M', 'A', 'S' ],
			[ 'S', 'A', 'M' ],
		];
		$rows    = count( $this->data );
		$cols    = strlen( $this->data[0] );
		$count   = 0;

		for ( $r = 1; $r < $rows - 1; $r ++ ) {
			for ( $c = 1; $c < $cols - 1; $c ++ ) {
				if ( $this->matches_pattern( $pattern, $r, $c ) ) {
					$count ++;
				}
			}
		}

		return $count;
	}

	/**
	 * Checks if a pattern exists with the given position as the center.
	 *
	 * @param array   $pattern The pattern to search for.
	 * @param integer $r       The center row of the pattern.
	 * @param integer $c       The center column of the pattern.
	 *
	 * @return bool
	 */
	private function matches_pattern(array $pattern, int $r, int $c): bool {
		foreach ($pattern as $top) {
			foreach ($pattern as $bottom) {
				if (
					$this->data[$r - 1][$c - 1] === $top[0] &&
					$this->data[$r][$c] === $top[1] &&
					$this->data[$r + 1][$c + 1] === $top[2] &&
					$this->data[$r + 1][$c - 1] === $bottom[0] &&
					$this->data[$r][$c] === $bottom[1] &&
					$this->data[$r - 1][$c + 1] === $bottom[2]
				) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Checks if a word can be found in the grid starting from a given position and moving in a given direction.
	 *
	 * @param string  $word The word to search for.
	 * @param integer $r    The starting row position.
	 * @param integer $c    The starting column position.
	 * @param integer $x    The row direction (delta).
	 * @param integer $y    The column direction (delta).
	 *
	 * @return bool True if the word is found, false otherwise.
	 */
	private function matches_word( string $word, int $r, int $c, int $x, int $y ): bool {
		$rows   = count( $this->data );
		$cols   = strlen( $this->data[0] );
		$length = strlen( $word );

		for ( $i = 0; $i < $length; $i ++ ) {
			$new_r = $r + $i * $x;
			$new_c = $c + $i * $y;

			// Check for bounds
			if ( $new_r < 0 || $new_r >= $rows || $new_c < 0 || $new_c >= $cols ) {
				return false;
			}

			// Check if the character matches
			if ( $this->data[ $new_r ][ $new_c ] !== $word[ $i ] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file   = $test ? '/data/day-04-test.txt' : '/data/day-04.txt';

		return explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
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
	$day04  = new Day04( $test, $part );
	$result = $day04->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 18,
			'real' => 2534,
		],
		2 => [
			'test' => 9,
			'real' => 1866,
		],
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";   // Reset text formatting

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
