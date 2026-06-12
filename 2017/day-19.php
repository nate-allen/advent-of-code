<?php

namespace AdventOfCode\Year2017;

/**
 * Day 19: A Series of Tubes
 */
class Day19 {
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
	 * @return string
	 */
	public function run(): string {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => (string) $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Follow the path and collect letters.
	 *
	 * @return string
	 */
	private function solve_part_1(): string {
		[ $letters ] = $this->walk();

		return $letters;
	}

	/**
	 * Part 2: Count the total steps.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		[ , $steps ] = $this->walk();

		return $steps;
	}

	/**
	 * Walk the path, collecting letters and counting steps.
	 *
	 * @return array [ letters, steps ]
	 */
	private function walk(): array {
		$grid = $this->data;

		// Find starting position: the '|' in the first row.
		$x   = strpos( $grid[0], '|' );
		$y   = 0;
		$dx  = 0;
		$dy  = 1; // Start moving down.
		$letters = '';
		$steps   = 0;

		while ( true ) {
			$x += $dx;
			$y += $dy;
			$steps++;

			// Out of bounds or empty space means we're done.
			if ( $y < 0 || $y >= count( $grid ) || $x < 0 || $x >= strlen( $grid[ $y ] ) ) {
				break;
			}

			$char = $grid[ $y ][ $x ];

			if ( $char === ' ' ) {
				break;
			}

			if ( $char === '+' ) {
				// Turn: try perpendicular directions (not backwards).
				if ( $dy !== 0 ) {
					// Was moving vertically, now move horizontally.
					if ( $x + 1 < strlen( $grid[ $y ] ) && $grid[ $y ][ $x + 1 ] !== ' ' ) {
						$dx = 1;
						$dy = 0;
					} else {
						$dx = -1;
						$dy = 0;
					}
				} else {
					// Was moving horizontally, now move vertically.
					if ( $y + 1 < count( $grid ) && isset( $grid[ $y + 1 ][ $x ] ) && $grid[ $y + 1 ][ $x ] !== ' ' ) {
						$dx = 0;
						$dy = 1;
					} else {
						$dx = 0;
						$dy = -1;
					}
				}
			} elseif ( ctype_alpha( $char ) ) {
				$letters .= $char;
			}
		}

		return [ $letters, $steps ];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-19-test.txt' : '/data/day-19.txt';

		return explode( "\n", rtrim( file_get_contents( __DIR__ . $file ), "\n" ) );
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
	$day19  = new Day19( $test, $part );
	$result = $day19->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 'ABCDEF',
			'real' => 'LOHMDQATP',
		],
		2 => [
			'test' => 38,
			'real' => 16492,
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
