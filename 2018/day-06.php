<?php

namespace AdventOfCode\Year2018;

/**
 * Day 06: Chronal Coordinates
 */
class Day06 {
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
	 * Part 1: Find the size of the largest finite area
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$coordinates = $this->data;

		// Calculate bounding box
		$min_x = min( array_column( $coordinates, 0 ) );
		$max_x = max( array_column( $coordinates, 0 ) );
		$min_y = min( array_column( $coordinates, 1 ) );
		$max_y = max( array_column( $coordinates, 1 ) );

		// Track area counts for each coordinate
		$area_counts = array_fill( 0, count( $coordinates ), 0 );
		// Track which coordinates are infinite (touch the boundary)
		$infinite = array_fill( 0, count( $coordinates ), false );

		// Iterate through all points in the bounding box
		for ( $x = $min_x; $x <= $max_x; $x++ ) {
			for ( $y = $min_y; $y <= $max_y; $y++ ) {
				$min_distance  = PHP_INT_MAX;
				$closest_index = -1;
				$is_tie        = false;

				// Calculate Manhattan distance to all coordinates
				foreach ( $coordinates as $index => $coord ) {
					$distance = abs( $x - $coord[0] ) + abs( $y - $coord[1] );

					if ( $distance < $min_distance ) {
						$min_distance  = $distance;
						$closest_index = $index;
						$is_tie        = false;
					} elseif ( $distance === $min_distance ) {
						// Tie detected - this point doesn't belong to any coordinate
						$is_tie = true;
					}
				}

				// If there's no tie, assign this point to the closest coordinate
				if ( ! $is_tie && $closest_index >= 0 ) {
					$area_counts[ $closest_index ]++;

					// Check if this point is on the boundary (mark coordinate as infinite)
					if ( $x === $min_x || $x === $max_x || $y === $min_y || $y === $max_y ) {
						$infinite[ $closest_index ] = true;
					}
				}
			}
		}

		// Find the maximum finite area
		$max_finite_area = 0;
		foreach ( $area_counts as $index => $count ) {
			if ( ! $infinite[ $index ] && $count > $max_finite_area ) {
				$max_finite_area = $count;
			}
		}

		return $max_finite_area;
	}

	/**
	 * Part 2: Find the size of the region where sum of distances to all coordinates < threshold
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$coordinates = $this->data;
		$num_coords  = count( $coordinates );

		// Use threshold 32 for test data (per example), 10000 for real data
		$threshold = $this->is_test ? 32 : 10000;

		// Calculate bounding box
		$min_x = min( array_column( $coordinates, 0 ) );
		$max_x = max( array_column( $coordinates, 0 ) );
		$min_y = min( array_column( $coordinates, 1 ) );
		$max_y = max( array_column( $coordinates, 1 ) );

		// Pad bounding box to ensure we don't miss any safe points
		// The farthest a point could be is threshold / number_of_points
		$padding = (int) ceil( $threshold / $num_coords );
		$min_x  -= $padding;
		$max_x  += $padding;
		$min_y  -= $padding;
		$max_y  += $padding;

		$safe_count = 0;

		// Iterate through all points in the padded bounding box
		for ( $x = $min_x; $x <= $max_x; $x++ ) {
			for ( $y = $min_y; $y <= $max_y; $y++ ) {
				$total_distance = 0;

				// Calculate sum of Manhattan distances to all coordinates
				foreach ( $coordinates as $coord ) {
					$total_distance += abs( $x - $coord[0] ) + abs( $y - $coord[1] );
				}

				// If sum is less than threshold, count this point
				if ( $total_distance < $threshold ) {
					$safe_count++;
				}
			}
		}

		return $safe_count;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of coordinates, each as [x, y]
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-06-test.txt' : '/data/day-06.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$coordinates = [];
		foreach ( $lines as $line ) {
			// Parse "x, y" format
			if ( preg_match( '/(\d+),\s*(\d+)/', $line, $matches ) ) {
				$coordinates[] = [ (int) $matches[1], (int) $matches[2] ];
			}
		}

		return $coordinates;
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
	$day06  = new Day06( $test, $part );
	$result = $day06->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 17,
			'real' => 4976,
		],
		2 => [
			'test' => 16,
			'real' => 46462,
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
