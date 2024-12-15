<?php

namespace AdventOfCode\Year2024;

/**
 * Day 15: Warehouse Woes
 */
class Day15 {
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
	 * Directions for moving around the warehouse.
	 *
	 * @var array
	 */
	private array $directions = [
		">" => [ 1, 0 ],
		"v" => [ 0, 1 ],
		"<" => [ - 1, 0 ],
		"^" => [ 0, - 1 ]
	];

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
			1, 2 => $this->solve(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1 and 2: What is the sum of all boxes' final GPS coordinates?
	 *
	 * @return int
	 */
	private function solve(): int {
		$current = $this->find_robot();
		$map     = $this->data['map'];
		$moves   = $this->data['moves'];

		foreach ( $moves as $move ) {
			list( $current, $map ) = $this->move( $map, $current, $move );
		}

		return $this->get_box_gps_sum( $map );
	}

	/**
	 * Finds the starting position of the robot.
	 *
	 * @return array
	 */
	private function find_robot(): array {
		foreach ( $this->data['map'] as $y => $row ) {
			$x = array_search( "@", $row );
			if ( $x !== false ) {
				return [ $x, $y ];
			}
		}
	}

	/**
	 * Simulates a single move of the robot and any obstacles it encounters in the warehouse.
	 * If a move is invalid due to walls (`#`) or blocked obstacles, the robot's position and map remain unchanged.
	 *
	 * @param array  $map     Map of the warehouse.
	 * @param array  $current The robot's current position.
	 * @param string $move    The direction of the movement.
	 *
	 * @return array
	 */
	private function move( array $map, array $current, string $move ): array {
		// Get the movement direction based on the move command.
		$direction = $this->directions[ $move ];

		// The object at the robot's current position.
		$object = $map[ $current[1] ][ $current[0] ];

		// The next position based on the direction of movement.
		$next = [ $current[0] + $direction[0], $current[1] + $direction[1] ];

		// What is at the next position?
		$obstacle = $map[ $next[1] ][ $next[0] ];

		// Check if the obstacle is a wide box.
		$wide_obstacle = $obstacle === "[" || $obstacle === "]";

		// Check if the movement is horizontal.
		$is_left_right = $direction[1] === 0;

		// Handle vertical movement into a wide box.
		if ( $wide_obstacle && ! $is_left_right ) {
			// Get the "sibling" tile of the wide box.
			$sibling_dir = $obstacle === "[" ? $this->directions[">"] : $this->directions["<"];
			$sibling     = [ $next[0] + $sibling_dir[0], $next[1] + $sibling_dir[1] ];

			// Attempt to move the wide box and its sibling in the same direction.
			$map_after_move = $this->move( $map, $next, $move )[1];
			$map_after_move = $this->move( $map_after_move, $sibling, $move )[1];

			// Check if the wide box and its sibling were successfully moved.
			// If the positions where they were are not empty, it wasn't successful.
			if ( $map_after_move[ $next[1] ][ $next[0] ] !== "." || $map_after_move[ $sibling[1] ][ $sibling[0] ] !== "." ) {
				// If either part of the wide box cannot move, the robot stays in place.
				return [ $current, $map ];
			}

			// Update the map to reflect the moved wide box.
			$map = $map_after_move;
		}

		// Handle regular boxes or wide boxes being pushed horizontally.
		if ( $obstacle === "O" || ( $wide_obstacle && $is_left_right ) ) {
			// Recursively try to move the box in the same direction.
			$map = $this->move( $map, $next, $move )[1];
		}

		// If the next position is a wall, the robot can't move.
		if ( $obstacle === "#" ) {
			return [ $current, $map ];
		}

		// Re-check the obstacle at the next position.
		// If the position is still not empty ('.'), the robot stays in place.
		if ( $map[ $next[1] ][ $next[0] ] !== "." ) {
			return [ $current, $map ];
		}

		// Update the map
		// Move the robot to the new position and clear the old position.
		$map[ $current[1] ][ $current[0] ] = ".";
		$map[ $next[1] ][ $next[0] ] = $object;

		// Return the robot's new position and the updated map.
		return [ $next, $map ];
	}

	/**
	 * Calculates the sum of all boxes' final GPS coordinates.
	 *
	 * @param array $map The warehouse map.
	 *
	 * @return int
	 */
	private function get_box_gps_sum( array $map ): int {
		$total = 0;

		foreach ( $map as $y => $row ) {
			for ( $x = 0; $x < count( $row ); $x ++ ) {
				$c = $row[ $x ];

				if ( $c !== "O" && $c !== "[" ) {
					continue;
				}

				$total += $x + $y * 100;
				if ( $c === "[" ) {
					$x ++;
				}
			}
		}

		return (int) $total;
	}

	/**
	 * Resizes the map by doubling its width.
	 *
	 * @param array $map The warehouse map.
	 *
	 * @return array
	 */
	function resize_map( array $map ): array {
		return array_map( function ( $row ) {
			$next_row = [];

			foreach ( $row as $cell ) {
				if ( $cell === "O" ) {
					array_push( $next_row, "[", "]" );
				} else if ( $cell === "@" ) {
					array_push( $next_row, "@", "." );
				} else {
					array_push( $next_row, $cell, $cell );
				}
			}

			return $next_row;
		}, $map );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-15-test.txt' : '/data/day-15.txt';
		list( $g, $m ) = explode( "\n\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$map = array_map( 'str_split', explode( "\n", trim( $g ) ) );

		if ( $this->part === 2 ) {
			$map = $this->resize_map( $map );
		}

		return [
			'map'   => $map,
			'moves' => str_split( implode( "", explode( "\n", trim( $m ) ) ) )
		];
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
	$day15  = new Day15( $test, $part );
	$result = $day15->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 10092,
			'real' => 1463512,
		],
		2 => [
			'test' => 9021,
			'real' => 1486520,
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
