<?php

namespace AdventOfCode\Year2025;

require_once __DIR__ . '/Shape.php';

/**
 * Day 12: Christmas Tree Farm (A Real Packing Solution)
 */
class Day12B {
	/**
	 * Parsed data from the input file.
	 *
	 * @var array
	 */
	private array $data;

	/**
	 * Whether to use test data.
	 *
	 * @var bool
	 */
	private bool $is_test;

	public function __construct( bool $test = false ) {
		$this->is_test = $test;
		$this->data    = $this->parse_data( $test );
	}

	/**
	 * Executes the puzzle.
	 *
	 * @return integer
	 */
	public function run(): int {
		return $this->solve_part_1();
	}

	/**
	 * Part 1: Count how many regions can fit all their required presents.
	 *
	 * Uses area check first (for real data), then backtracking/DFS for verification.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$shapes  = $this->data['shapes'];
		$regions = $this->data['regions'];

		$count = 0;

		foreach ( $regions as $region ) {
			$width  = $region['width'];
			$height = $region['height'];
			$counts = $region['counts'];

			// Calculate total area needed
			$total_area_needed = 0;
			foreach ( $counts as $shape_index => $quantity ) {
				$total_area_needed += $shapes[ $shape_index ]->getArea() * $quantity;
			}

			// Calculate available area
			$available_area = $width * $height;

			// Quick area check: if not enough area, skip
			if ( $total_area_needed > $available_area ) {
				continue;
			}

			// Build list of shapes to place
			$shapes_to_place = [];
			foreach ( $counts as $shape_index => $quantity ) {
				for ( $i = 0; $i < $quantity; $i++ ) {
					$shapes_to_place[] = $shapes[ $shape_index ];
				}
			}

			// Sort by area (largest first) to fail fast
			usort( $shapes_to_place, function( $a, $b ) {
				return $b->getArea() <=> $a->getArea();
			} );

			// Try to pack all shapes
			if ( $this->can_fit( $width, $height, $shapes_to_place ) ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Determines if all shapes can fit in the given region using backtracking.
	 *
	 * @param  int    $width  Region width.
	 * @param  int    $height Region height.
	 * @param  array  $shapes Array of Shape objects to place.
	 * @return bool True if all shapes can fit.
	 */
	private function can_fit( int $width, int $height, array $shapes ): bool {
		if ( empty( $shapes ) ) {
			return true; // All shapes placed
		}

		$occupied = [];
		return $this->try_place_remaining_shapes( $width, $height, $shapes, 0, $occupied );
	}

	/**
	 * Tries to place all remaining shapes starting from the first shape.
	 *
	 * @param  int    $width    Region width.
	 * @param  int    $height   Region height.
	 * @param  array  $shapes   Array of Shape objects to place.
	 * @param  int    $index    Current shape index.
	 * @param  array  $occupied Set of occupied cells (key: "x,y").
	 * @return bool True if all remaining shapes can be placed.
	 */
	private function try_place_remaining_shapes( int $width, int $height, array $shapes, int $index, array &$occupied ): bool {
		if ( $index >= count( $shapes ) ) {
			return true; // All shapes placed
		}

		$shape        = $shapes[ $index ];
		$orientations = $shape->getOrientations();

		// Try each orientation
		foreach ( $orientations as $orientation_data ) {
			$orientation = $orientation_data['coords'];
			$max_x       = $orientation_data['max_x'];
			$max_y       = $orientation_data['max_y'];

			// Early exit if shape is too large for the region
			if ( $max_x >= $width || $max_y >= $height ) {
				continue;
			}

			// Try each position where the shape fits
			for ( $y = 0; $y <= $height - $max_y - 1; $y++ ) {
				for ( $x = 0; $x <= $width - $max_x - 1; $x++ ) {
					// Check for overlaps first (before building array)
					$valid = true;
					foreach ( $orientation as $coord ) {
						$cell_x = $x + $coord[0];
						$cell_y = $y + $coord[1];
						$key    = "$cell_x,$cell_y";

						if ( isset( $occupied[ $key ] ) ) {
							$valid = false;
							break;
						}
					}

					if ( ! $valid ) {
						continue;
					}

					// Build array of cells
					$new_cells = [];
					foreach ( $orientation as $coord ) {
						$cell_x      = $x + $coord[0];
						$cell_y      = $y + $coord[1];
						$new_cells[] = "$cell_x,$cell_y";
					}

					// Place the shape
					foreach ( $new_cells as $key ) {
						$occupied[ $key ] = true;
					}

					// Try to place next shape
					if ( $this->try_place_remaining_shapes( $width, $height, $shapes, $index + 1, $occupied ) ) {
						return true;
					}

					// Remove the shape
					foreach ( $new_cells as $key ) {
						unset( $occupied[ $key ] );
					}
				}
			}
		}

		return false; // No valid placement found
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param  bool $test Whether to use test data.
	 * @return array
	 */
	private function parse_data( bool $test = false ): array {
		$file    = $test ? '/data/day-12-test.txt' : '/data/day-12.txt';
		$content = file_get_contents( __DIR__ . $file );

		$sections = explode( "\n\n", trim( $content ) );

		$regions_text = array_pop( $sections );
		$shapes_text  = $sections;

		// Parse shapes into Shape objects
		$shapes = [];
		foreach ( $shapes_text as $shape_text ) {
			$lines = explode( "\n", trim( $shape_text ) );
			// First line is "N:", skip it
			$pattern  = implode( "\n", array_slice( $lines, 1 ) );
			$shapes[] = new Shape( $pattern );
		}

		// Parse regions
		$regions      = [];
		$region_lines = explode( "\n", trim( $regions_text ) );
		foreach ( $region_lines as $line ) {
			// Format: "WxH: count0 count1 count2 ..."
			if ( preg_match( '/^(\d+)x(\d+):\s*(.+)$/', $line, $matches ) ) {
				$width     = (int) $matches[1];
				$height    = (int) $matches[2];
				$counts    = array_map( 'intval', explode( ' ', trim( $matches[3] ) ) );
				$regions[] = [
					'width' => $width,
					'height' => $height,
					'counts' => $counts,
				];
			}
		}

		return [
			'shapes' => $shapes,
			'regions' => $regions,
		];
	}
}

/**
 * Runs the puzzle with the given settings and outputs results.
 *
 * @param bool $test Whether to use test data.
 */
function run_puzzle( bool $test ): void {
	$start  = microtime( true );
	$day12  = new Day12B( $test );
	$result = $day12->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		'test' => 2,
		'real' => 481,
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $test ? $expected_values['test'] : $expected_values['real'] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for test mode
while ( true ) {
	$test = strtolower( trim( readline( 'Do you want to run the test? (y/n): ' ) ) );
	if ( in_array( $test, [ 'y', 'n' ], true ) ) {
		$test_mode = $test === 'y';
		run_puzzle( $test_mode );
		break;
	}
	echo 'Invalid input. Please enter y or n.' . PHP_EOL;
}
