<?php

namespace AdventOfCode\Year2021;

/**
 * Day 09: Smoke Basin
 */
class Day09 {
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
	 * Part 1: What is the sum of the risk levels of all low points on the heat map?
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$low_points  = $this->get_low_points();
		$risk_levels = array_map( fn( $point ) => $point['value'] + 1, $low_points );

		return array_sum( $risk_levels );
	}

	/**
	 * Part 2: Find the product of the sizes of the three largest basins.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$low_points = $this->get_low_points();
		$visited    = array_fill( 0, count( $this->data ), array_fill( 0, count( $this->data[0] ), false ) );

		// Calculate basin sizes
		$basin_sizes = [];
		foreach ( $low_points as $point ) {
			$basin_sizes[] = $this->calculate_basin_size( $point['i'], $point['j'], $visited );
		}

		// Sort the basin sizes, so we can get the three largest.
		rsort( $basin_sizes );

		// Multiply together the three largest basins.
		return array_product( array_slice( $basin_sizes, 0, 3 ) );
	}

	/**
	 * Finds all low points in the heightmap.
	 *
	 * @return array An array of low points with their coordinates and values.
	 */
	private function get_low_points(): array {
		$low_points = [];

		for ( $i = 0; $i < count( $this->data ); $i ++ ) {
			for ( $j = 0; $j < count( $this->data[0] ); $j ++ ) {
				$point     = $this->data[ $i ][ $j ];
				$neighbors = [
					[ $i - 1, $j ],
					[ $i + 1, $j ],
					[ $i, $j - 1 ],
					[ $i, $j + 1 ],
				];

				$is_low_point = true;

				foreach ( $neighbors as $neighbor ) {
					$ni = $neighbor[0];
					$nj = $neighbor[1];

					// Skip if the neighbor is out of bounds.
					if ( $ni < 0 || $ni >= count( $this->data ) || $nj < 0 || $nj >= count( $this->data[0] ) ) {
						continue;
					}

					// Skip if the neighbor is higher than the current point.
					if ( $this->data[ $ni ][ $nj ] <= $point ) {
						$is_low_point = false;
						break;
					}
				}

				if ( $is_low_point ) {
					$low_points[] = [ 'i' => $i, 'j' => $j, 'value' => $point ];
				}
			}
		}

		return $low_points;
	}

	/**
	 * Calculates the size of a basin starting from a given low point.
	 *
	 * @param integer $start_i Row index of the starting point.
	 * @param integer $start_j Column index of the starting point.
	 * @param array   $visited The visited array to track explored points.
	 *
	 * @return int
	 */
	private function calculate_basin_size( int $start_i, int $start_j, array &$visited ): int {
		// Create a queue with the starting point.
		$queue = [ [ $start_i, $start_j ] ];
		$size  = 0;

		// Continue exploring while there are points in the queue.
		while ( ! empty( $queue ) ) {
			// Get the next point to process from the queue.
			[ $i, $j ] = array_pop( $queue );

			// Skip if the point is out of bounds.
			if ( $i < 0 || $i >= count( $this->data ) || $j < 0 || $j >= count( $this->data[0] ) ) {
				continue;
			}

			// Skip if the point has already been visited or is height 9.
			if ( $visited[ $i ][ $j ] || $this->data[ $i ][ $j ] === 9 ) {
				continue;
			}

			// Mark the point as visited and increase the basin size.
			$visited[ $i ][ $j ] = true;
			$size++;

			// Add the neighboring points to the queue for further exploration.
			$queue[] = [ $i - 1, $j ]; // Up
			$queue[] = [ $i + 1, $j ]; // Down
			$queue[] = [ $i, $j - 1 ]; // Left
			$queue[] = [ $i, $j + 1 ]; // Right
		}

		return $size;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-09-test.txt' : '/data/day-09.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( function ( $line ) {
			return array_map( 'intval', str_split( $line ) );
		}, $lines );
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
	$day09  = new Day09( $test, $part );
	$result = $day09->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 15,
			'real' => 489,
		],
		2 => [
			'test' => 1134,
			'real' => 1056330,
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
