<?php

namespace AdventOfCode\Year2019;

include_once 'lib/IntcodeComputer.php';

/**
 * Day 15: Oxygen System
 */
class Day15 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var integer
	 */
	private int $part;

	/**
	 * Parsed data from the input file.
	 *
	 * @var array
	 */
	private array $data;

	/**
	 * Map of the area. Keys are "x,y" coordinates.
	 * Mark: 0 = wall, 1 = open space, 2 = oxygen system.
	 *
	 * @var array
	 */
	private array $map = [];

	/**
	 * Array to keep track of visited positions and their best step counts.
	 *
	 * @var array
	 */
	private array $visited = [];

	/**
	 * Movement directions.
	 * Command => [dx, dy] (1: north, 2: south, 3: west, 4: east)
	 *
	 * @var array
	 */
	private array $directions = [
		1 => [ 0, - 1 ],
		2 => [ 0, 1 ],
		3 => [ - 1, 0 ],
		4 => [ 1, 0 ],
	];

	/**
	 * The fewest number of steps required to reach the oxygen system.
	 *
	 * @var int
	 */
	private int $min_steps = PHP_INT_MAX;

	public function __construct( int $part ) {
		$this->part = $part;
		$this->data = $this->parse_data();
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
	 * Part 1: Find the fewest number of movement commands required to reach the oxygen system.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		// Initialize the Intcode computer with the droid's program.
		$computer = new IntcodeComputer( $this->data );

		// Set the starting position in the map.
		$this->map["0,0"] = 1; // Starting point.

		// Start exploring from the starting position (0,0) with 0 moves.
		$this->search_oxygen_path( 0, 0, 0, $computer );

		return $this->min_steps;
	}

	/**
	 * Part 2: Determine how many minutes it will take to fill the area with oxygen.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		// Initialize the Intcode computer with the droid's program.
		$computer = new IntcodeComputer( $this->data );

		// Set the starting position in the map.
		$this->map["0,0"] = 1;

		// Explore the entire area
		$this->search_oxygen_path( 0, 0, 0, $computer );

		// Find the oxygen system starting position (cell with value 2).
		$oxygen_pos = null;
		foreach ( $this->map as $pos => $value ) {
			if ( $value === 2 ) {
				$oxygen_pos = $pos;
				break;
			}
		}

		list( $ox, $oy ) = explode( ',', $oxygen_pos );

		// Simulate the spread of oxygen using BFS.
		$queue             = [];
		$queue[]           = [ 'x' => (int) $ox, 'y' => (int) $oy, 'minute' => 0 ];
		$filled            = [];
		$filled["$ox,$oy"] = true;
		$last_minute       = 0;

		while ( ! empty( $queue ) ) {
			$current     = array_shift( $queue );
			$cx          = $current['x'];
			$cy          = $current['y'];
			$minute      = $current['minute'];
			$last_minute = max( $last_minute, $minute );

			// Check adjacent positions.
			foreach ( $this->directions as $delta ) {
				$nx  = $cx + $delta[0];
				$ny  = $cy + $delta[1];
				$key = "$nx,$ny";

				// If the cell is open (or the oxygen system) and not yet filled, fill it.
				if ( isset( $this->map[ $key ] ) && $this->map[ $key ] > 0 && ! isset( $filled[ $key ] ) ) {
					$filled[ $key ] = true;
					$queue[]        = [ 'x' => $nx, 'y' => $ny, 'minute' => $minute + 1 ];
				}
			}
		}

		return $last_minute;
	}

	/**
	 * Recursively explores the maze to find the oxygen system.
	 *
	 * @param integer         $x        Current x coordinate.
	 * @param integer         $y        Current y coordinate.
	 * @param integer         $steps    Number of moves taken so far.
	 * @param IntcodeComputer $computer Current state of the Intcode computer.
	 *
	 * @return void
	 */
	private function search_oxygen_path( int $x, int $y, int $steps, IntcodeComputer $computer ): void {
		$pos_key = "$x,$y";

		// If we've already visited this cell in fewer or equal steps, skip further processing.
		if ( isset( $this->visited[ $pos_key ] ) && $this->visited[ $pos_key ] <= $steps ) {
			return;
		}

		$this->visited[ $pos_key ] = $steps;

		// If the current cell is the oxygen system, update our minimum steps.
		if ( isset( $this->map[ $pos_key ] ) && $this->map[ $pos_key ] === 2 ) {
			if ( $steps < $this->min_steps ) {
				$this->min_steps = $steps;
			}
			// return;
			// Continue exploring in case another branch finds a shorter path.
		}

		// Prune this branch if the current step count already exceeds the known minimum.
		if ( $steps >= $this->min_steps ) {
			return;
		}

		// Try every possible direction.
		foreach ( $this->directions as $command => $delta ) {
			$new_x   = $x + $delta[0];
			$new_y   = $y + $delta[1];
			$new_key = "$new_x,$new_y";

			// If this new position has been reached more efficiently before, skip it.
			if ( isset( $this->visited[ $new_key ] ) && $this->visited[ $new_key ] <= $steps + 1 ) {
				continue;
			}

			// Clone the Intcode computer so that each branch maintains its own state.
			$clone = clone $computer;
			$clone->add_input( $command );
			$status = $clone->run_until_output();

			// Status 0 means the droid hit a wall. Mark the map accordingly and skip further exploration.
			if ( $status === 0 ) {
				$this->map[ $new_key ] = 0;
				continue;
			}

			// 1 for open space, 2 for the oxygen system.
			$this->map[ $new_key ] = $status;

			// Explore from the new cell.
			$this->search_oxygen_path( $new_x, $new_y, $steps + 1, $clone );
		}
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @return array
	 */
	private function parse_data(): array {
		return array_map( 'intval', explode( ",", trim( file_get_contents( __DIR__ . '/data/day-15.txt' ) ) ) );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 */
function run_part( int $part ): void {
	$start  = microtime( true );
	$day15  = new Day15( $part );
	$result = $day15->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => 262,
		2 => 314,
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $expected_values[ $part ] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for part and test mode
while ( true ) {
	$part = (int) trim( readline( 'Which part do you want to run? (1/2): ' ) );
	if ( ! in_array( $part, [ 1, 2 ], true ) ) {
		echo 'Invalid part. Please enter 1 or 2.' . PHP_EOL;
		continue;
	}

	run_part( $part );
	break;
}
