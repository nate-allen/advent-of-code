<?php

namespace AdventOfCode\Year2018;

/**
 * Day 11: Chronal Charge
 */
class Day11 {
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
	 * Parsed data from the input file (serial number).
	 *
	 * @var int
	 */
	private int $serial;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->serial  = $this->parse_data( $this->is_test );
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return string
	 */
	public function run(): string {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Find the 3x3 square with the largest total power.
	 *
	 * Uses a summed-area table (integral image) for efficient rectangle sum queries.
	 *
	 * @return string The X,Y coordinate of the top-left cell (e.g., "21,61")
	 */
	private function solve_part_1(): string {
		$grid_size   = 300;
		$square_size = 3;

		$power = $this->build_power_grid( $grid_size );
		$sum   = $this->build_summed_area_table( $power, $grid_size );

		// Find the best 3x3 square
		$max_sum = PHP_INT_MIN;
		$best_x  = 0;
		$best_y  = 0;

		// Check all valid top-left positions for a 3x3 square
		for ( $y = 1; $y <= $grid_size - $square_size + 1; $y++ ) {
			for ( $x = 1; $x <= $grid_size - $square_size + 1; $x++ ) {
				// Calculate sum of square from (x,y) to (x+2,y+2)
				// Using summed-area table: sum[y2][x2] - sum[y1-1][x2] - sum[y2][x1-1] + sum[y1-1][x1-1]
				$y2         = $y + $square_size - 1;
				$x2         = $x + $square_size - 1;
				$square_sum = $sum[$y2][$x2] 
					- $sum[$y - 1][$x2] 
					- $sum[$y2][$x - 1] 
					+ $sum[$y - 1][$x - 1];

				if ( $square_sum > $max_sum ) {
					$max_sum = $square_sum;
					$best_x  = $x;
					$best_y  = $y;
				}
			}
		}

		return $best_x . ',' . $best_y;
	}

	/**
	 * Part 2: Find the square of any size (1x1 to 300x300) with the largest total power.
	 *
	 * @return string The X,Y,size identifier of the top-left cell and size (e.g., "232,251,12")
	 */
	private function solve_part_2(): string {
		$grid_size = 300;

		$power = $this->build_power_grid( $grid_size );
		$sum   = $this->build_summed_area_table( $power, $grid_size );

		// Find the best square of any size
		$max_sum   = PHP_INT_MIN;
		$best_x    = 0;
		$best_y    = 0;
		$best_size = 0;

		// Loop through all possible square sizes from 1 to 300
		for ( $size = 1; $size <= $grid_size; $size++ ) {
			// Check all valid top-left positions for a square of this size
			for ( $y = 1; $y <= $grid_size - $size + 1; $y++ ) {
				for ( $x = 1; $x <= $grid_size - $size + 1; $x++ ) {
					// Calculate sum of square from (x,y) to (x+size-1,y+size-1)
					// Using summed-area table: sum[y2][x2] - sum[y1-1][x2] - sum[y2][x1-1] + sum[y1-1][x1-1]
					$y2         = $y + $size - 1;
					$x2         = $x + $size - 1;
					$square_sum = $sum[$y2][$x2] 
						- $sum[$y - 1][$x2] 
						- $sum[$y2][$x - 1] 
						+ $sum[$y - 1][$x - 1];

					if ( $square_sum > $max_sum ) {
						$max_sum   = $square_sum;
						$best_x    = $x;
						$best_y    = $y;
						$best_size = $size;
					}
				}
			}
		}

		return $best_x . ',' . $best_y . ',' . $best_size;
	}

	/**
	 * Builds the power grid for all fuel cells.
	 *
	 * @param int $grid_size The size of the grid (300x300).
	 *
	 * @return array The power grid with 1-indexed coordinates [y][x] = power level
	 */
	private function build_power_grid( int $grid_size ): array {
		$power = [];
		for ( $y = 1; $y <= $grid_size; $y++ ) {
			for ( $x = 1; $x <= $grid_size; $x++ ) {
				$rack_id       = $x + 10;
				$p             = $rack_id * $y + $this->serial;
				$p             = $p * $rack_id;
				$p             = (int)($p / 100) % 10;
				$p             = $p - 5;
				$power[$y][$x] = $p;
			}
		}
		return $power;
	}

	/**
	 * Builds a summed-area table (integral image) for efficient rectangle sum queries.
	 *
	 * @param array $power     The power grid with 1-indexed coordinates.
	 * @param int   $grid_size The size of the grid (300x300).
	 *
	 * @return array The summed-area table with 0-indexed coordinates and padding.
	 *               sum[y][x] = sum of all cells from (1,1) to (y,x)
	 */
	private function build_summed_area_table( array $power, int $grid_size ): array {
		$sum = [];
		// Initialize padding row and column with zeros
		for ( $i = 0; $i <= $grid_size; $i++ ) {
			$sum[0][$i] = 0;
			$sum[$i][0] = 0;
		}

		for ( $y = 1; $y <= $grid_size; $y++ ) {
			for ( $x = 1; $x <= $grid_size; $x++ ) {
				$sum[$y][$x] = $power[$y][$x] 
					+ $sum[$y - 1][$x] 
					+ $sum[$y][$x - 1] 
					- $sum[$y - 1][$x - 1];
			}
		}
		return $sum;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return int The grid serial number
	 */
	private function parse_data( bool $test ): int {
		$file    = $test ? '/data/day-11-test.txt' : '/data/day-11.txt';
		$content = trim( file_get_contents( __DIR__ . $file ) );

		return (int) $content;
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
	$day11  = new Day11( $test, $part );
	$result = $day11->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => '21,61',
			'real' => '243,43'
		],
		2 => [
			'test' => '232,251,12',
			'real' => '236,151,15',
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
