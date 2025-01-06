<?php

namespace AdventOfCode\Year2021;

/**
 * Day 23: Amphipod
 */
class Day23 {
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
	private array $rooms;

	/**
	 * Array of empty values representing the empty hallway.
	 *
	 * @var array
	 */
	private array $hallway;

	/**
	 * Distances from each room to each valid spot in the hallway.
	 *
	 * @var array
	 */
	private array $room_distances = [
		[ 2, 1, 1, 3, 5, 7, 8 ],
		[ 4, 3, 1, 1, 3, 5, 6 ],
		[ 6, 5, 3, 1, 1, 3, 4 ],
		[ 8, 7, 5, 3, 1, 1, 2 ],
	];

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->rooms   = $this->parse_data( $this->is_test );
		$this->hallway = array_fill( 0, 7, null );
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
	 * Part 1: Find the minimum cost to move all Amphipods to their correct rooms.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->solve( $this->rooms, $this->hallway );
	}

	/**
	 * Part 2: Find the minimum cost to move all Amphipods to their correct rooms with additional Amphipods.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		// More amphipods that need to be added.
		$amphipods = [ [ 3, 3 ], [ 2, 1 ], [ 1, 0 ], [ 0, 2 ] ];
		$new_rooms = [];

		// The new amphipods need to be added between the existing ones in each room.
		foreach ( $this->rooms as $i => $room ) {
			$new_rooms[] = array_merge( [ array_shift( $room ) ], $amphipods[ $i ], [ array_pop( $room ) ] );
		}

		return $this->solve( $new_rooms, $this->hallway, 4 );
	}

	/**
	 * Solves the puzzle using recursion.
	 *
	 * @param array   $rooms     The current state of Amphipods in rooms.
	 * @param array   $hallway   The current state of Amphipods in the hallway.
	 * @param integer $room_size The number of Amphipods in each room.
	 *
	 * @return integer
	 */
	private function solve( array $rooms, array $hallway, int $room_size = 2 ) {
		static $cache = [];

		// Create a cache key out of the rooms, hallway, and room size.
		$key = json_encode( [ $rooms, $hallway, $room_size ] );

		// If we've already seen this state, return the cached result.
		if ( isset( $cache[ $key ] ) ) {
			return $cache[ $key ];
		}

		if ( $this->is_done( $rooms, $room_size ) ) {
			return 0;
		}

		$best = PHP_INT_MAX;

		foreach ( $this->possible_moves( $rooms, $hallway, $room_size ) as [$cost, $next_state] ) {
			[ $next_rooms, $next_hallway ] = $next_state;
			$cost += $this->solve( $next_rooms, $next_hallway, $room_size );
			$best = min( $best, $cost );
		}

		return $cache[ $key ] = $best;
	}

	/**
	 * Checks if all Amphipods are in the correct rooms.
	 *
	 * @param array   $rooms     The current state of Amphipods in rooms.
	 * @param integer $room_size The number of Amphipods in each room.
	 *
	 * @return bool
	 */
	private function is_done( array $rooms, int $room_size ): bool {
		foreach ( $rooms as $r => $room ) {
			if ( count( $room ) !== $room_size || array_filter( $room, fn( $o ) => $o !== $r ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Generates all possible moves for Amphipods from rooms to the hallway or vice versa.
	 *
	 * @param array   $rooms     The current state of Amphipods in rooms.
	 * @param array   $hallway   The current state of Amphipods in the hallway.
	 * @param integer $room_size The number of Amphipods in each room.
	 *
	 * @return array
	 */
	private function possible_moves( array $rooms, array $hallway, int $room_size ): array {
		return array_merge(
			$this->moves_to_room( $rooms, $hallway, $room_size ),
			$this->moves_to_hallway( $rooms, $hallway, $room_size )
		);
	}

	/**
	 * Calculates the cost of moving an Amphipod from a room to the hallway or vice versa.
	 *
	 * @param array   $room      The current state of Amphipods in the room.
	 * @param array   $hallway   The current state of Amphipods in the hallway.
	 * @param integer $r         The index of the room.
	 * @param integer $h         The index of the hallway spot.
	 * @param integer $room_size The number of Amphipods in each room.
	 * @param bool    $to_room   Whether the Amphipod is moving to the room.
	 *
	 * @return float|int
	 */
	private function move_cost( array $room, array $hallway, int $r, int $h, int $room_size, bool $to_room = false ): float|int {
		// $h is the index of the hallway spot and $r is the room index.

		if ( $r + 1 < $h ) {
			$start = $r + 2;
			$end   = $h + ( ! $to_room );
		} else {
			$start = $h + $to_room;
			$end   = $r + 2;
		}

		// Check if the spots between the room and the hallway spot are clear.
		for ( $i = $start; $i < $end; $i ++ ) {
			if ( $hallway[ $i ] !== null ) {
				return PHP_INT_MAX;
			}
		}

		// If moving to the room, the amphipod is in the hallway at spot $h.
		// Otherwise, it's the first amphipod in the room.
		$obj = $to_room ? $hallway[ $h ] : $room[0];

		return pow( 10, $obj ) * ( $this->room_distances[ $r ][ $h ] + ( $to_room + $room_size - count( $room ) ) );
	}

	/**
	 * Generates all possible moves for Amphipods from the hallway to rooms.
	 *
	 * @param array   $rooms     The current state of Amphipods in rooms.
	 * @param array   $hallway   The current state of Amphipods in the hallway.
	 * @param integer $room_size The number of Amphipods in each room.
	 *
	 * @return array
	 */
	private function moves_to_room( array $rooms, array $hallway, int $room_size ): array {
		$results = [];

		foreach ( $hallway as $h => $amphipod ) {
			// Skip empty hallway spots.
			if ( $amphipod === null ) {
				continue;
			}

			// Check the Amphipod's room. If it contains a different Amphipod, skip it.
			$room = $rooms[ $amphipod ];
			if ( array_filter( $room, fn( $o ) => $o !== $amphipod ) ) {
				continue;
			}

			// Calculate the cost of moving the Amphipod from this hallway spot to its room.
			$cost = $this->move_cost( $room, $hallway, $amphipod, $h, $room_size, true );

			// If it's not possible to move the Amphipod to the room, skip this move.
			if ( $cost === PHP_INT_MAX ) {
				continue;
			}

			// Create a new state where this Amphipod is moved from this hallway spot to its room.
			$new_rooms         = $rooms;
			$new_rooms[ $amphipod ] = array_merge( [ $amphipod ], $room );
			$new_hallway       = $hallway;
			$new_hallway[ $h ] = null;

			$results[] = [ $cost, [ $new_rooms, $new_hallway ] ];
		}

		return $results;
	}

	/**
	 * Generates all possible moves for Amphipods from rooms to the hallway.
	 *
	 * @param array   $rooms     The current state of Amphipods in rooms.
	 * @param array   $hallway   The current state of Amphipods in the hallway.
	 * @param integer $room_size The number of Amphipods in each room.
	 *
	 * @return array
	 */
	private function moves_to_hallway( array $rooms, array $hallway, int $room_size ): array {
		$results = [];

		foreach ( $rooms as $r => $room ) {
			// If all Amphipods in this room are in the correct one, skip them.
			if ( count( array_filter( $room, fn( $o ) => $o === $r ) ) === count( $room ) ) {
				continue;
			}

			for ( $h = 0; $h < count( $hallway ); $h ++ ) {
				// Calculate the cost of moving the Amphipod from this room to this hallway spot.
				$cost = $this->move_cost( $room, $hallway, $r, $h, $room_size );

				// If it's not possible to move the Amphipod to the hallway, skip this move.
				if ( $cost === PHP_INT_MAX ) {
					continue;
				}

				// Create a new state where this Amphipod is moved from this room to this hallway spot.
				$new_rooms = $rooms;
				array_shift( $new_rooms[ $r ] );
				$new_hallway       = $hallway;
				$new_hallway[ $h ] = $room[0];

				$results[] = [ $cost, [ $new_rooms, $new_hallway ] ];
			}
		}

		return $results;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-23-test.txt' : '/data/day-23.txt';
		$lines = array_slice( explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) ), 2, 2 );

		$data = [];

		// Amphipods are represented as 0-3 to correspond to the room they belong to.
		foreach ( [ 3, 5, 7, 9 ] as $pos ) { // Room positions are columns 3, 5, 7, 9
			$room = array_map(
				fn( $line ) => ord( $line[ $pos ] ) - 65, // Convert A-D to 0-3 using ASCII
				$lines
			);

			$data[] = $room;
		}

		return $data;
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
	$day23  = new Day23( $test, $part );
	$result = $day23->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 12521,
			'real' => 13455,
		],
		2 => [
			'test' => 44169,
			'real' => 43567,
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
