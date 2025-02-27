<?php

namespace AdventOfCode\Year2019;

// increase the memory limit
ini_set( 'memory_limit', '1024M' );

/**
 * Day 18: Many-Worlds Interpretation
 */
class Day18 {
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

	/**
	 * Constructor.
	 *
	 * @param bool $test Whether test data should be used.
	 * @param int $part The part of the puzzle to run (1 or 2).
	 */
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
	 * Part 1: Collect all the keys. Keys (lowercase letters) unlock doors (uppercase letters) of the same letter.
	 *
	 * @return int The minimum number of steps to collect all keys.
	 */
	private function solve_part_1(): int {
		// Build a grid from the input data.
		$grid = [];
		foreach ( $this->data as $line ) {
			$grid[] = str_split( $line );
		}
		$rows = count( $grid );
		$cols = strlen( $this->data[0] );

		$start_x       = 0;
		$start_y       = 0;
		$all_keys_mask = 0;

		// Identify the starting position and the total set of keys.
		for ( $i = 0; $i < $rows; $i ++ ) {
			for ( $j = 0; $j < $cols; $j ++ ) {
				$cell = $grid[ $i ][ $j ];
				if ( $cell === '@' ) {
					$start_x = $i;
					$start_y = $j;
				} elseif ( ctype_lower( $cell ) ) {
					$all_keys_mask |= 1 << ( ord( $cell ) - ord( 'a' ) );
				}
			}
		}

		// Use a queue for BFS; each state is: [row, col, keys_bitmask, steps]
		$queue = new \SplQueue();
		$queue->enqueue( [ $start_x, $start_y, 0, 0 ] );

		// Use an associative array to mark visited states: "x,y,keys"
		$visited                        = [];
		$visited["$start_x,$start_y,0"] = true;

		// Define movements: up, down, left, right.
		$directions = [
			[ - 1, 0 ],
			[ 1, 0 ],
			[ 0, - 1 ],
			[ 0, 1 ]
		];

		while ( ! $queue->isEmpty() ) {
			list( $x, $y, $keys, $steps ) = $queue->dequeue();
			$current_cell = $grid[ $x ][ $y ];

			// If we are on a key, pick it up if not already collected.
			if ( ctype_lower( $current_cell ) ) {
				$bit = 1 << ( ord( $current_cell ) - ord( 'a' ) );
				if ( ( $keys & $bit ) === 0 ) {
					$keys |= $bit;
					// If all keys have been collected, return the steps taken.
					if ( $keys === $all_keys_mask ) {
						return $steps;
					}
					// Mark the new state with the updated keys.
					$visited["$x,$y,$keys"] = true;
				}
			}

			// Explore adjacent positions.
			foreach ( $directions as $dir ) {
				$new_x = $x + $dir[0];
				$new_y = $y + $dir[1];

				// Check grid boundaries.
				if ( $new_x < 0 || $new_x >= $rows || $new_y < 0 || $new_y >= $cols ) {
					continue;
				}

				$new_cell = $grid[ $new_x ][ $new_y ];
				// Skip walls.
				if ( $new_cell === '#' ) {
					continue;
				}

				// If the cell is a door, ensure we have the corresponding key.
				if ( ctype_upper( $new_cell ) ) {
					$required_key_bit = 1 << ( ord( strtolower( $new_cell ) ) - ord( 'a' ) );
					if ( ( $keys & $required_key_bit ) === 0 ) {
						continue;
					}
				}

				$state_key = "$new_x,$new_y,$keys";
				if ( ! isset( $visited[ $state_key ] ) ) {
					$visited[ $state_key ] = true;
					$queue->enqueue( [ $new_x, $new_y, $keys, $steps + 1 ] );
				}
			}
		}

		// Should never get here if the input is valid.
		return - 1;
	}

	/**
	 * Part 2: Collect all keys with four robots in the fewest steps.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		// Build the grid.
		$grid = array_map( fn( $line ) => str_split( $line ), $this->data );
		$rows = count( $grid );
		$cols = count( $grid[0] );

		// Find the original starting position '@'.
		$start_x = - 1;
		$start_y = - 1;
		for ( $i = 0; $i < $rows; $i ++ ) {
			for ( $j = 0; $j < $cols; $j ++ ) {
				if ( $grid[ $i ][ $j ] === '@' ) {
					$start_x = $i;
					$start_y = $j;
					break 2;
				}
			}
		}
		// Patch the grid to create four starting positions.
		// Replace the 3x3 area centered at the original '@' with:
		//   @#@
		//   ###
		//   @#@
		$grid[ $start_x - 1 ][ $start_y - 1 ] = '@';
		$grid[ $start_x - 1 ][ $start_y ]     = '#';
		$grid[ $start_x - 1 ][ $start_y + 1 ] = '@';
		$grid[ $start_x ][ $start_y - 1 ]     = '#';
		$grid[ $start_x ][ $start_y ]         = '#';
		$grid[ $start_x ][ $start_y + 1 ]     = '#';
		$grid[ $start_x + 1 ][ $start_y - 1 ] = '@';
		$grid[ $start_x + 1 ][ $start_y ]     = '#';
		$grid[ $start_x + 1 ][ $start_y + 1 ] = '@';

		// Create nodes for each of the four new starting positions.
		$nodes       = [];
		$nodes['@0'] = [ $start_x - 1, $start_y - 1 ];
		$nodes['@1'] = [ $start_x - 1, $start_y + 1 ];
		$nodes['@2'] = [ $start_x + 1, $start_y - 1 ];
		$nodes['@3'] = [ $start_x + 1, $start_y + 1 ];

		// Also add all keys.
		for ( $i = 0; $i < $rows; $i ++ ) {
			for ( $j = 0; $j < $cols; $j ++ ) {
				$cell = $grid[ $i ][ $j ];
				if ( ctype_lower( $cell ) ) {
					$nodes[ $cell ] = [ $i, $j ];
				}
			}
		}

		// Build the graph.
		$graph = [];
		foreach ( $nodes as $node => $pos ) {
			$graph[ $node ] = $this->bfs_from_node( $grid, $pos );
		}

		// Compute bitmask for all keys.
		$all_keys_mask = 0;
		foreach ( $nodes as $node => $_ ) {
			if ( ctype_lower( $node ) ) {
				$all_keys_mask |= 1 << ( ord( $node ) - ord( 'a' ) );
			}
		}
		$cache = [];

		// Starting positions for the four robots.
		$start_positions = [ '@0', '@1', '@2', '@3' ];

		return $this->find_shortest_path( $start_positions, 0, $graph, $all_keys_mask, $cache );
	}

	/**
	 * Performs a BFS from a starting position to compute all reachable keys.
	 *
	 * @param array $grid      2D maze grid.
	 * @param array $start_pos [x, y] starting coordinates.
	 *
	 * @return array
	 */
	private function bfs_from_node( array $grid, array $start_pos ): array {
		$queue   = new \SplQueue();
		$rows    = count( $grid );
		$cols    = count( $grid[0] );
		$visited = [];
		$start_x = $start_pos[0];
		$start_y = $start_pos[1];

		$queue->enqueue( [ $start_x, $start_y, 0, 0 ] ); // x, y, distance, door mask
		$visited["$start_x,$start_y"] = [ 0 ];

		$found = [];

		while ( ! $queue->isEmpty() ) {
			list( $x, $y, $dist, $doors ) = $queue->dequeue();
			$cell = $grid[ $x ][ $y ];
			// If we encounter a key (and it's not the starting cell), record it.
			if ( ctype_lower( $cell ) && ! ( $x === $start_x && $y === $start_y ) ) {
				if ( ! isset( $found[ $cell ] ) || $dist < $found[ $cell ]['distance'] ) {
					$found[ $cell ] = [ 'distance' => $dist, 'doors' => $doors ];
				}
			}

			// Explore adjacent cells.
			foreach ( [ [ 0, 1 ], [ 1, 0 ], [ 0, - 1 ], [ - 1, 0 ] ] as $dir ) {
				$nx = $x + $dir[0];
				$ny = $y + $dir[1];

				if ( $nx < 0 || $nx >= $rows || $ny < 0 || $ny >= $cols ) {
					continue;
				}

				if ( $grid[ $nx ][ $ny ] === '#' ) {
					continue;
				}

				$n_doors = $doors;
				$ncell   = $grid[ $nx ][ $ny ];

				if ( ctype_upper( $ncell ) ) {
					// Add door requirement (bit corresponding to the lowercase letter).
					$n_doors |= 1 << ( ord( strtolower( $ncell ) ) - ord( 'a' ) );
				}

				$key  = "$nx,$ny";
				$skip = false;

				if ( isset( $visited[ $key ] ) ) {
					foreach ( $visited[ $key ] as $mask ) {
						// If we've been here with a door mask that's a subset, skip.
						if ( ( $mask | $n_doors ) === $mask ) {
							$skip = true;
							break;
						}
					}
				}

				if ( $skip ) {
					continue;
				}

				$visited[ $key ][] = $n_doors;
				$queue->enqueue( [ $nx, $ny, $dist + 1, $n_doors ] );
			}
		}

		return $found;
	}

	/**
	 * Recursively computes the shortest path to collect all keys.
	 *
	 * @param array   $positions     Array of current node labels (one per robot).
	 * @param integer $keys          Bitmask of keys already collected.
	 * @param array   $graph         Reduced graph of nodes.
	 * @param integer $all_keys_mask Bitmask representing all keys.
	 * @param array   $cache         Memoization cache.
	 *
	 * @return int
	 */
	private function find_shortest_path( array $positions, int $keys, array $graph, int $all_keys_mask, array &$cache ): int {
		if ( $keys === $all_keys_mask ) {
			return 0;
		}

		$cache_key = implode( ',', $positions ) . '|' . $keys;
		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		$min = PHP_INT_MAX;
		// For each robot, try collecting a key reachable from its current position.
		foreach ( $positions as $i => $current ) {
			foreach ( $graph[ $current ] as $next => $edge ) {
				$bit = 1 << ( ord( $next ) - ord( 'a' ) );

				if ( $keys & $bit ) {
					continue;
				}

				if ( ( $edge['doors'] & ~$keys ) !== 0 ) {
					continue;
				}

				$new_positions       = $positions;
				$new_positions[ $i ] = $next;
				$steps               = $edge['distance'] + $this->find_shortest_path( $new_positions, $keys | $bit, $graph, $all_keys_mask, $cache );

				if ( $steps < $min ) {
					$min = $steps;
				}
			}
		}

		$cache[ $cache_key ] = $min;

		return $min;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-18-test.txt' : '/data/day-18.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return $lines;
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
	$day18  = new Day18( $test, $part );
	$result = $day18->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 86,
			'real' => 4204,
		],
		2 => [
			'test' => 9223372036854775807,
			'real' => 1682,
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
