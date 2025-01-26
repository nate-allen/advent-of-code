<?php

namespace AdventOfCode\Year2020;

/**
 * Day 17: Conway Cubes
 */
class Day17 {
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
	 * Part 1: Simulate 6 cycles in a 3D grid. How many cubes are left in the active state?
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->simulate_energy_source( 3 );
	}

	/**
	 * Part 2: Simulate 6 cycles in a 4D grid. How many cubes are left in the active state?
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->simulate_energy_source( 4 );
	}

	/**
	 * Simulates the energy source for the specified number of dimensions.
	 *
	 * @param integer $dimensions The number of dimensions to simulate.
	 *
	 * @return integer
	 */
	private function simulate_energy_source( int $dimensions ): int {
		$active_cubes = [];

		// Parse the initial state
		foreach ( $this->data as $y => $line ) {
			for ( $x = 0; $x < strlen( $line ); $x ++ ) {
				if ( $line[ $x ] === '#' ) {
					$coordinates = [ $x, $y ];
					for ( $d = 2; $d < $dimensions; $d ++ ) {
						$coordinates[] = 0; // Initialize additional dimensions as 0
					}
					$active_cubes[ implode( ',', $coordinates ) ] = true;
				}
			}
		}

		// Perform six boot cycles
		for ( $cycle = 0; $cycle < 6; $cycle ++ ) {
			$new_active_cubes = [];
			$cubes_to_check   = [];

			// Determine cubes to check for state changes
			foreach ( $active_cubes as $cube => $value ) {
				$coordinates = explode( ',', $cube );
				foreach ( $this->generate_neighbor_offsets( $dimensions ) as $offsets ) {
					$neighbor = array_map( 'array_sum', array_map( null, $coordinates, $offsets ) );

					$cubes_to_check[ implode( ',', $neighbor ) ] = true;
				}
			}

			// Determine the state of each cube
			foreach ( $cubes_to_check as $cube => $value ) {
				$coordinates      = explode( ',', $cube );
				$active_neighbors = 0;

				foreach ( $this->generate_neighbor_offsets( $dimensions ) as $offsets ) {
					$neighbor     = array_map( 'array_sum', array_map( null, $coordinates, $offsets ) );
					$neighbor_key = implode( ',', $neighbor );
					if ( isset( $active_cubes[ $neighbor_key ] ) ) {
						$active_neighbors ++;
					}
				}

				// Apply the rules for cube activation
				$is_currently_active = isset( $active_cubes[ $cube ] );
				if ( ! $is_currently_active && $active_neighbors === 3 ) {
					$new_active_cubes[ $cube ] = true;
				} elseif ( $is_currently_active && in_array( $active_neighbors, [ 2, 3 ] ) ) {
					$new_active_cubes[ $cube ] = true;
				}
			}

			$active_cubes = $new_active_cubes;
		}

		return count( $active_cubes );
	}

	/**
	 * Generates the neighbor offsets for the specified number of dimensions.
	 *
	 * @param integer $dimensions The number of dimensions.
	 *
	 * @return array
	 */
	private function generate_neighbor_offsets( int $dimensions ): array {
		$ranges  = [ - 1, 0, 1 ];
		$offsets = [ [] ];

		for ( $i = 0; $i < $dimensions; $i ++ ) {
			$new_offsets = [];
			foreach ( $offsets as $offset ) {
				foreach ( $ranges as $delta ) {
					$new_offsets[] = array_merge( $offset, [ $delta ] );
				}
			}
			$offsets = $new_offsets;
		}

		// Remove the offset representing no change (all zeros)
		return array_filter( $offsets, function ( $offset ) {
			return array_sum( array_map( 'abs', $offset ) ) > 0;
		} );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-17-test.txt' : '/data/day-17.txt';

		return explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
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
	$day17  = new Day17( $test, $part );
	$result = $day17->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 112,
			'real' => 382,
		],
		2 => [
			'test' => 848,
			'real' => 2552,
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
