<?php

namespace AdventOfCode\Year2024;

/**
 * Day 20: Race Condition
 */
class Day20 {
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
	 * The minimum time saved by cheating to be considered valid.
	 *
	 * @var int
	 */
	private int $goal;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );

		// When it's not a test, the goal is 100. For tests, it depends on which part we're solving.
		if ( ! $this->is_test ) {
			$this->goal = 100;
		} else {
			$this->goal = $this->part === 1 ? 1 : 50; // Part 1 is any time saved, part 2 is at least 50.
		}
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
	 * Part 1: Cheats last 2 picoseconds. How many cheats would save you at least 100 picoseconds?
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		return $this->find_cheats( 2 );
	}

	/**
	 * Part 2: Cheats last 20 picoseconds. How many cheats would save you at least 100 picoseconds?
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		return $this->find_cheats( 20 );
	}

	/**
	 * Calculates the number of valid "cheat" paths that save at least 100 picoseconds.
	 *
	 * @param integer $max_cheat_steps Maximum number of steps allowed while cheating.
	 *
	 * @return int
	 */
	private function find_cheats( int $max_cheat_steps ): int {
		$grid  = $this->data;
		$start = $this->find_position( $grid, 'S' );
		$end   = $this->find_position( $grid, 'E' );

		// Get the normal travel time without cheats.
		$normal_time = $this->shortest_path( $grid, $start, $end );

		// Get the distances from start and end to all reachable points (without cheats).
		$distances_from_start = $this->calculate_distances( $grid, $start, false );
		$distances_to_end     = $this->calculate_distances( $grid, $end, false );

		$saved_times = 0;

		// Iterate over all cells in the grid to evaluate potential cheat opportunities.
		foreach ( $grid as $y => $row ) {
			foreach ( $row as $x => $cell ) {
				// Skip walls ('#') and cells not reachable from the start without cheating.
				if ( $cell === '#' || ! isset( $distances_from_start["$x,$y"] ) ) {
					continue;
				}

				// Distance from the start to this point.
				$start_dist = $distances_from_start["$x,$y"];

				// Calculate all points reachable from this position while cheating.
				$reachable = $this->calculate_distances( $grid, [ $x, $y ], true, $max_cheat_steps );

				foreach ( $reachable as $coordinate => $cheat_steps ) {
					[ $cheat_x, $cheat_y ] = explode( ',', $coordinate );

					// Skip walls or points that cannot connect to the end.
					if ( $grid[ $cheat_y ][ $cheat_x ] === '#' || ! isset( $distances_to_end[ $coordinate ] ) ) {
						continue;
					}

					// Calculate the total time using this cheat path.
					$end_dist   = $distances_to_end[ $coordinate ]; // Distance from cheat exit to end.
					$cheat_time = $start_dist + $cheat_steps + $end_dist; // Total time with cheat.
					$time_saved = $normal_time - $cheat_time; // Time saved by this cheat.

					// Increment count if the cheat saves at least 100 picoseconds.
					if ( $time_saved >= $this->goal ) {
						$saved_times++;
					}
				}
			}
		}

		return $saved_times; // Return the total count of valid cheats.
	}

	/**
	 * Finds the coordinates of a target character in the grid.
	 *
	 * @param array  $grid   The grid represented as a 2D array of characters.
	 * @param string $target The character to find in the grid.
	 *
	 * @return array
	 */
	private function find_position( array $grid, string $target ): array {
		foreach ( $grid as $y => $row ) {
			foreach ( $row as $x => $cell ) {
				if ( $cell === $target ) {
					return [ $x, $y ];
				}
			}
		}

		// Couldn't find the target. This shouldn't happen.
		echo "Couldn't find '$target'." . PHP_EOL;
		return [ - 1, - 1 ];
	}

	/**
	 * Finds the shortest path from the start to the end in the grid using BFS.
	 *
	 * @param array $grid  The grid.
	 * @param array $start The starting coordinates.
	 * @param array $end   The ending coordinates.
	 *
	 * @return int
	 */
	private function shortest_path( array $grid, array $start, array $end ): int {
		// Initialize a queue for BFS with the starting position and step count.
		$queue   = [ [ $start[0], $start[1], 0 ] ];
		$visited = []; // Cache visited positions.

		while ( ! empty( $queue ) ) {
			// Dequeue the next position to process.
			[ $x, $y, $steps ] = array_shift( $queue );

			// Check if the current position matches the end position.
			if ( $x === $end[0] && $y === $end[1] ) {
				return $steps; // Return the step count when the end is reached.
			}

			// Skip this position if it has already been visited.
			if ( isset( $visited["$x,$y"] ) ) {
				continue;
			}
			$visited["$x,$y"] = true; // Mark the position as visited.

			// Enqueue all valid neighbors of the current position.
			foreach ( $this->get_neighbors( $grid, $x, $y, false ) as [$nx, $ny] ) {
				$queue[] = [ $nx, $ny, $steps + 1 ];
			}
		}

		// reached the end of the queue.
		return - 1;
	}

	/**
	 * Calculates distances from a starting point to all reachable positions in the grid.
	 *
	 * This method uses Breadth-First Search (BFS) to compute the number of steps
	 * required to reach each position from the start. It optionally allows passing
	 * through walls for a specified cutoff distance.
	 *
	 * @param array   $grid        The grid.
	 * @param array   $start       The starting coordinates.
	 * @param bool    $allow_walls Whether to allow passing through walls.
	 * @param integer $cutoff      The maximum distance to calculate.
	 *
	 * @return array
	 */
	private function calculate_distances( array $grid, array $start, bool $allow_walls, int $cutoff = PHP_INT_MAX ): array {
		// Initialize a queue for BFS with the starting position and step count.
		$queue = [ [ $start[0], $start[1], 0 ] ];
		$distances = []; // Track the shortest distance to each reachable position.

		while ( ! empty( $queue ) ) {
			// Get and remove next position to process.
			[ $x, $y, $steps ] = array_shift( $queue );

			// Skip positions already visited or more than the cutoff distance.
			if ( isset( $distances[ "$x,$y" ] ) || $steps > $cutoff ) {
				continue;
			}

			// Number of steps to reach this position.
			$distances[ "$x,$y" ] = $steps;

			// Enqueue all valid neighbors of the current position.
			foreach ( $this->get_neighbors( $grid, $x, $y, $allow_walls ) as [$nx, $ny] ) {
				$queue[] = [ $nx, $ny, $steps + 1 ];
			}
		}

		return $distances;
	}

	/**
	 * Retrieves all valid neighboring positions for a given grid cell.
	 *
	 * This method calculates the neighboring positions that are within bounds
	 * of the grid. It optionally allows passing through walls ('#') based on
	 * the $allow_walls parameter.
	 *
	 * @param array   $grid        The grid.
	 * @param integer $x           The x of the current position.
	 * @param integer $y           The y of the current position.
	 * @param bool    $allow_walls Whether to include walls as valid neighbors.
	 *
	 * @return array
	 */
	private function get_neighbors( array $grid, int $x, int $y, bool $allow_walls ): array {
		$directions = [ [ 0, 1 ], [ 1, 0 ], [ 0, -1 ], [ -1, 0 ] ]; // Up, right, down, left
		$neighbors = [];

		// Iterate through all possible directions.
		foreach ( $directions as [$dx, $dy] ) {
			$nx = $x + $dx; // The neighbor's x-coordinate.
			$ny = $y + $dy; // The neighbor's y-coordinate.

			// Check if the neighbor is within the grid bounds.
			if ( $nx >= 0 && $ny >= 0 && $ny < count( $grid ) && $nx < count( $grid[0] ) ) {
				// Include the neighbor if walls are allowed or it is not a wall.
				if ( $allow_walls || $grid[ $ny ][ $nx ] !== '#' ) {
					$neighbors[] = [ $nx, $ny ];
				}
			}
		}

		return $neighbors;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-20-test.txt' : '/data/day-20.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( fn( $line ) => str_split( $line ), $lines );
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
	$day20  = new Day20( $test, $part );
	$result = $day20->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 44,
			'real' => 1511,
		],
		2 => [
			'test' => 285,
			'real' => 1020507,
		],
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting`

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
