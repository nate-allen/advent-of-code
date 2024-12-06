<?php

namespace AdventOfCode\Year2024;

/**
 * Day 06: Guard Gallivant
 */
class Day06 {
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
	 * Mapping of directions.
	 *
	 * @var array
	 */
	private array $directions = [
		'^' => [ - 1, 0 ],
		'>' => [ 0, 1 ],
		'v' => [ 1, 0 ],
		'<' => [ 0, - 1 ],
	];

	/**
	 * Mapping of turning right.
	 *
	 * @var array
	 */
	private array $turn_right = [
		'^' => '>',
		'>' => 'v',
		'v' => '<',
		'<' => '^',
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
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: How many distinct positions will the guard visit before leaving the mapped area?
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$map  = $this->data;
		$rows = count( $map );
		$cols = strlen( $map[0] );
		[ $guard_position, $guard_direction ] = $this->find_guard( $map );

		$visited_positions = [ "{$guard_position[0]},{$guard_position[1]}" ];

		while ( true ) {
			[ $row, $col ] = $this->directions[ $guard_direction ];
			$next_position = [ $guard_position[0] + $row, $guard_position[1] + $col ];

			if ( $this->is_out_of_map( $next_position, $rows, $cols ) ) {
				break;
			}

			if ( $map[ $next_position[0] ][ $next_position[1] ] === '#' ) {
				$guard_direction = $this->turn_right[ $guard_direction ];
			} else {
				$guard_position      = $next_position;
				$visited_positions[] = "{$guard_position[0]},{$guard_position[1]}";
			}
		}

		return count( array_unique( $visited_positions ) );
	}

	/**
	 * Part 2: Place an obstruction to create a loop. How many possible positions are there to place the obstruction?
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$map  = $this->data;
		$rows = count( $map );
		$cols = strlen( $map[0] );
		[ $guard_position, $guard_direction ] = $this->find_guard( $map );

		$valid_positions = $this->get_valid_positions( $map, $rows, $cols );
		$loop_positions  = 0;

		foreach ( $valid_positions as [$r, $c] ) {
			$map_copy             = $map;
			$map_copy[ $r ][ $c ] = '#';

			if ( $this->check_loop( $map_copy, $guard_position, $guard_direction ) ) {
				$loop_positions ++;
			}
		}

		return $loop_positions;
	}

	/**
	 * Finds the guard's initial position and direction.
	 *
	 * @param array $map The map of the area.
	 *
	 * @return array An array containing the guard's position and direction.
	 */
	private function find_guard( array $map ): array {
		$guard_direction = '^';

		foreach ( $map as $r => $line ) {
			$c = strpos( $line, $guard_direction );
			if ( $c !== false ) {
				return [ [ $r, $c ], $guard_direction ];
			}
		}

		// Guard not found. This should not happen.
		return [ [ 0, 0 ], $guard_direction ];
	}

	/**
	 * Checks if a position is out of the map.
	 *
	 * @param array   $position The position to check.
	 * @param integer $rows     The number of rows in the map.
	 * @param integer $cols     The number of columns in the map.
	 *
	 * @return bool True if the position is out of bounds, false otherwise.
	 */
	private function is_out_of_map( array $position, int $rows, int $cols ): bool {
		[ $r, $c ] = $position;

		return $r < 0 || $r >= $rows || $c < 0 || $c >= $cols;
	}

	/**
	 * Gets valid positions for placing an obstruction.
	 *
	 * @param array   $map  The map of the area.
	 * @param integer $rows The number of rows in the map.
	 * @param integer $cols The number of columns in the map.
	 *
	 * @return array The candidate positions.
	 */
	private function get_valid_positions( array $map, int $rows, int $cols ): array {
		$candidate_positions = [];

		foreach ( $map as $r => $line ) {
			for ( $c = 0; $c < $cols; $c ++ ) {
				if ( $line[ $c ] === '.' ) {
					$candidate_positions[] = [ $r, $c ];
				}
			}
		}

		return $candidate_positions;
	}

	/**
	 * Checks if the guard creates a loop when patrolling.
	 *
	 * @param array  $map             The map of the area.
	 * @param array  $guard_position  The guard's initial position.
	 * @param string $guard_direction The guard's initial direction.
	 *
	 * @return bool True if a loop is detected, false otherwise.
	 */
	private function check_loop( array $map, array $guard_position, string $guard_direction ): bool {
		$visited           = [];
		$current_position  = $guard_position;
		$current_direction = $guard_direction;

		while ( true ) {
			[ $row, $col ] = $this->directions[ $current_direction ];
			$next_position = [ $current_position[0] + $row, $current_position[1] + $col ];

			if ( $this->is_out_of_map( $next_position, count( $map ), strlen( $map[0] ) ) ) {
				return false;
			}

			$key = "{$next_position[0]},{$next_position[1]},{$current_direction}";
			if ( isset( $visited[ $key ] ) ) {
				return true;
			}

			$visited[ $key ] = true;

			if ( $map[ $next_position[0] ][ $next_position[1] ] === '#' ) {
				$current_direction = $this->turn_right[ $current_direction ];
			} else {
				$current_position = $next_position;
			}
		}
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-06-test.txt' : '/data/day-06.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return $lines;
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
	$day06  = new Day06( $test, $part );
	$result = $day06->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 41,
			'real' => 4776,
		],
		2 => [
			'test' => 6,
			'real' => 1586,
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
