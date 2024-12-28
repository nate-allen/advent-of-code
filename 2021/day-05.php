<?php

namespace AdventOfCode\Year2021;

/**
 * Day 05: Hydrothermal Venture
 */
class Day05 {
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
	 * Part 1: Analyze a list of coordinates representing horizontal and vertical line segments on an ocean floor and
	 * determine the number of points where at least two lines overlap.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->calculate_overlaps();
	}

	/**
	 * Part 2: Also consider diagonal lines. Calculate the number of points where at least two lines overlap.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->calculate_overlaps( true );
	}

	/**
	 * Calculates overlaps for the given part.
	 *
	 * @param bool $consider_diagonals Whether to consider diagonal lines.
	 *
	 * @return int
	 */
	private function calculate_overlaps( bool $consider_diagonals = false ): int {
		$grid = [];

		foreach ( $this->data as $line ) {
			[ $x1, $y1 ] = $line[0];
			[ $x2, $y2 ] = $line[1];

			if ( $x1 === $x2 ) {
				// Vertical line
				$start = min( $y1, $y2 );
				$end   = max( $y1, $y2 );
				for ( $y = $start; $y <= $end; $y ++ ) {
					$grid["$x1,$y"] = ( $grid["$x1,$y"] ?? 0 ) + 1;
				}
			} elseif ( $y1 === $y2 ) {
				// Horizontal line
				$start = min( $x1, $x2 );
				$end   = max( $x1, $x2 );
				for ( $x = $start; $x <= $end; $x ++ ) {
					$grid["$x,$y1"] = ( $grid["$x,$y1"] ?? 0 ) + 1;
				}
			} elseif ( $consider_diagonals ) {
				// Diagonal line: Only include for part 2
				$x_step = $x1 < $x2 ? 1 : - 1;
				$y_step = $y1 < $y2 ? 1 : - 1;

				$x = $x1;
				$y = $y1;
				while ( true ) {
					$grid["$x,$y"] = ( $grid["$x,$y"] ?? 0 ) + 1;
					if ( $x === $x2 && $y === $y2 ) {
						break;
					}
					$x += $x_step;
					$y += $y_step;
				}
			}
		}

		// Count points where at least two lines overlap
		$overlap = 0;
		foreach ( $grid as $count ) {
			if ( $count >= 2 ) {
				$overlap ++;
			}
		}

		return $overlap;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-05-test.txt' : '/data/day-05.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$result = [];
		foreach ( $lines as $line ) {
			$points      = explode( ' -> ', $line );
			$coordinates = array_map( function ( $point ) {
				return array_map( 'intval', explode( ',', $point ) );
			}, $points );
			$result[]    = $coordinates;
		}

		return $result;
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
	$day05  = new Day05( $test, $part );
	$result = $day05->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 5,
			'real' => 6189,
		],
		2 => [
			'test' => 12,
			'real' => 19164,
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
