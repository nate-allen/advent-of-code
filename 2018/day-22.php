<?php

namespace AdventOfCode\Year2018;

/**
 * Day 22: Mode Maze
 */
class Day22 {
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

	/**
	 * The depth of the cave system.
	 *
	 * @var integer
	 */
	private int $depth;

	/**
	 * The target X coordinate.
	 *
	 * @var integer
	 */
	private int $target_x;

	/**
	 * The target Y coordinate.
	 *
	 * @var integer
	 */
	private int $target_y;

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
	 * Part 1: Calculate the total risk level for all regions in the rectangle
	 * from (0,0) to target coordinates.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		// Calculate erosion levels for all regions from (0,0) to target
		$erosion_levels = $this->calculate_erosion_levels( $this->target_x, $this->target_y );

		// Sum risk levels (erosion_level % 3) for all regions in the rectangle
		$total_risk = 0;
		for ( $y = 0; $y <= $this->target_y; $y++ ) {
			for ( $x = 0; $x <= $this->target_x; $x++ ) {
				$total_risk += $erosion_levels[$x][$y] % 3;
			}
		}

		return $total_risk;
	}

	/**
	 * Part 2: Find the shortest path from (0,0) to target with torch equipped,
	 * considering tool restrictions and switching costs.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		// Add buffer area beyond target (optimal path may go beyond target)
		$buffer = 100;
		$max_x  = $this->target_x + $buffer;
		$max_y  = $this->target_y + $buffer;

		// Calculate erosion levels for the extended area
		$erosion_levels = $this->calculate_erosion_levels( $max_x, $max_y );

		// Tool constants: 0 = torch, 1 = climbing gear, 2 = neither
		$TORCH         = 0;
		$CLIMBING_GEAR = 1;
		$NEITHER       = 2;

		// Priority queue: array of [time, x, y, tool]
		$queue = [ [ 0, 0, 0, $TORCH ] ];

		// Visited states: (x, y, tool) => best time
		$visited                                         = [];
		$visited[ $this->get_state_key( 0, 0, $TORCH ) ] = 0;

		// Directions: up, right, down, left
		$directions = [ [ 0, -1 ], [ 1, 0 ], [ 0, 1 ], [ -1, 0 ] ];

		while ( ! empty( $queue ) ) {
			// Sort queue by time (simple priority queue)
			usort( $queue, function( $a, $b ) {
				return $a[0] - $b[0];
			} );

			// Get state with minimum time
			list( $time, $x, $y, $tool ) = array_shift( $queue );
			$state_key                   = $this->get_state_key( $x, $y, $tool );

			// Skip if we've found a better path to this state
			if ( isset( $visited[ $state_key ] ) && $visited[ $state_key ] < $time ) {
				continue;
			}

			// Check if we've reached the target with torch
			if ( $x === $this->target_x && $y === $this->target_y && $tool === $TORCH ) {
				return $time;
			}

			// Get current region type
			$current_region = $erosion_levels[$x][$y] % 3;

			// Try moving to adjacent regions
			foreach ( $directions as $dir ) {
				$nx = $x + $dir[0];
				$ny = $y + $dir[1];

				// Skip if out of bounds
				if ( $nx < 0 || $ny < 0 || $nx > $max_x || $ny > $max_y ) {
					continue;
				}

				$next_region = $erosion_levels[$nx][$ny] % 3;

				// Check if current tool is valid in next region
				if ( $this->is_tool_valid( $tool, $next_region ) ) {
					$new_time   = $time + 1;
					$next_state = $this->get_state_key( $nx, $ny, $tool );

					// Add to queue if we haven't visited or found a better path
					if ( ! isset( $visited[ $next_state ] ) || $visited[ $next_state ] > $new_time ) {
						$visited[ $next_state ] = $new_time;
						$queue[]                = [ $new_time, $nx, $ny, $tool ];
					}
				}
			}

			// Try switching tools (if new tool is valid in current region)
			$allowed_tools = $this->get_allowed_tools( $current_region );
			foreach ( $allowed_tools as $new_tool ) {
				if ( $new_tool === $tool ) {
					continue; // Already have this tool
				}

				$new_time   = $time + 7;
				$next_state = $this->get_state_key( $x, $y, $new_tool );

				// Add to queue if we haven't visited or found a better path
				if ( ! isset( $visited[ $next_state ] ) || $visited[ $next_state ] > $new_time ) {
					$visited[ $next_state ] = $new_time;
					$queue[]                = [ $new_time, $x, $y, $new_tool ];
				}
			}
		}

		// Should never reach here
		return -1;
	}

	/**
	 * Gets a unique key for a state (x, y, tool).
	 *
	 * @param int $x X coordinate.
	 * @param int $y Y coordinate.
	 * @param int $tool Tool (0=torch, 1=climbing gear, 2=neither).
	 *
	 * @return string State key.
	 */
	private function get_state_key( int $x, int $y, int $tool ): string {
		return "$x,$y,$tool";
	}

	/**
	 * Checks if a tool is valid for a given region type.
	 *
	 * @param int $tool Tool (0=torch, 1=climbing gear, 2=neither).
	 * @param int $region_type Region type (0=rocky, 1=wet, 2=narrow).
	 *
	 * @return bool True if tool is valid for the region.
	 */
	private function is_tool_valid( int $tool, int $region_type ): bool {
		$allowed = $this->get_allowed_tools( $region_type );
		return in_array( $tool, $allowed, true );
	}

	/**
	 * Gets the allowed tools for a given region type.
	 *
	 * @param int $region_type Region type (0=rocky, 1=wet, 2=narrow).
	 *
	 * @return array Array of allowed tool IDs.
	 */
	private function get_allowed_tools( int $region_type ): array {
		// Rocky (0): torch or climbing gear
		// Wet (1): climbing gear or neither
		// Narrow (2): torch or neither
		return match ( $region_type ) {
			0 => [ 0, 1 ], // Rocky: torch, climbing gear
			1 => [ 1, 2 ], // Wet: climbing gear, neither
			2 => [ 0, 2 ], // Narrow: torch, neither
			default => [],
		};
	}

	/**
	 * Calculates erosion levels for all regions from (0,0) to (max_x, max_y).
	 * Uses ordered calculation (left-to-right, top-to-bottom) for efficiency.
	 *
	 * @param int $max_x Maximum X coordinate to calculate.
	 * @param int $max_y Maximum Y coordinate to calculate.
	 *
	 * @return array 2D array of erosion levels indexed by [x][y].
	 */
	private function calculate_erosion_levels( int $max_x, int $max_y ): array {
		$erosion_levels = [];

		// Calculate in order: left-to-right, top-to-bottom
		for ( $y = 0; $y <= $max_y; $y++ ) {
			for ( $x = 0; $x <= $max_x; $x++ ) {
				$geologic_index         = $this->get_geologic_index( $x, $y, $erosion_levels );
				$erosion_levels[$x][$y] = ($geologic_index + $this->depth) % 20183;
			}
		}

		return $erosion_levels;
	}

	/**
	 * Calculates the geologic index for a given coordinate.
	 *
	 * @param int   $x              X coordinate.
	 * @param int   $y              Y coordinate.
	 * @param array $erosion_levels Pre-calculated erosion levels (for dependencies).
	 *
	 * @return integer The geologic index.
	 */
	private function get_geologic_index( int $x, int $y, array $erosion_levels ): int {
		// Special case: (0,0) and target have geologic index 0
		if (($x === 0 && $y === 0) || ($x === $this->target_x && $y === $this->target_y)) {
			return 0;
		}

		// If Y coordinate is 0, geologic index is X * 16807
		if ( $y === 0 ) {
			return $x * 16807;
		}

		// If X coordinate is 0, geologic index is Y * 48271
		if ( $x === 0 ) {
			return $y * 48271;
		}

		// Otherwise: multiply erosion levels of (x-1, y) and (x, y-1)
		// Note: We multiply erosion levels, not geologic indices
		return $erosion_levels[$x - 1][$y] * $erosion_levels[$x][$y - 1];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-22-test.txt' : '/data/day-22.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		// Extract depth from first line: "depth: 510"
		preg_match( '/depth:\s*(\d+)/', $lines[0], $depth_matches );
		$this->depth = (int) $depth_matches[1];

		// Extract target coordinates from second line: "target: 10,10"
		preg_match( '/target:\s*(\d+),(\d+)/', $lines[1], $target_matches );
		$this->target_x = (int) $target_matches[1];
		$this->target_y = (int) $target_matches[2];

		return $lines;
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
	$day22  = new Day22( $test, $part );
	$result = $day22->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 114,
			'real' => 5637,
		],
		2 => [
			'test' => 45,
			'real' => 969,
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
