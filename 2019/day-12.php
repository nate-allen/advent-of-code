<?php

namespace AdventOfCode\Year2019;

/**
 * Day 12: The N-Body Problem
 */
class Day12 {
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
	private array $data = [];

	/**
	 * Moons data structure.
	 *
	 * @var array
	 */
	private array $moons = [];

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
		$this->initialize_moons();
	}

	/**
	 * Initializes moons with positions and velocity.
	 */
	private function initialize_moons(): void {
		foreach ( $this->data as $moon ) {
			$this->moons[] = [
				'pos' => $moon,
				'vel' => [ 0, 0, 0 ] // Initial velocity is [0, 0, 0]
			];
		}
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
	 * Part 1: Simulate the motion of moons for 1000 steps and calculate the total energy.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$steps = $this->is_test ? 100 : 1000;

		for ( $i = 0; $i < $steps; $i ++ ) {
			$this->apply_gravity();
			$this->apply_velocity();
		}

		return $this->calculate_total_energy();
	}

	/**
	 * Part 2: Determine the number of steps before the system returns to its initial state.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		// Store initial state separately for comparison
		$initial_state = $this->moons;

		$cycle_steps = [ 0, 0, 0 ]; // Cycle length for x, y, z axes
		$step        = 0;

		while ( in_array( 0, $cycle_steps, true ) ) {
			$step ++;
			$this->apply_gravity();
			$this->apply_velocity();

			// Check cycle completion for each axis
			for ( $axis = 0; $axis < 3; $axis ++ ) {
				if ( $cycle_steps[ $axis ] === 0 ) {
					$matches_initial = true;
					foreach ( $this->moons as $i => $moon ) {
						if ( $moon['pos'][ $axis ] !== $initial_state[ $i ]['pos'][ $axis ] ||
							 $moon['vel'][ $axis ] !== $initial_state[ $i ]['vel'][ $axis ] ) {
							$matches_initial = false;
							break;
						}
					}
					if ( $matches_initial ) {
						$cycle_steps[ $axis ] = $step;
					}
				}
			}
		}

		// Calculate the least common multiple (LCM) of the cycle lengths
		return gmp_intval( gmp_lcm( gmp_lcm( $cycle_steps[0], $cycle_steps[1] ), $cycle_steps[2] ) );
	}

	/**
	 * Applies gravity to adjust velocities based on the positions of moons.
	 */
	private function apply_gravity(): void {
		$moon_count = count( $this->moons );

		for ( $i = 0; $i < $moon_count - 1; $i ++ ) {
			for ( $j = $i + 1; $j < $moon_count; $j ++ ) {
				for ( $axis = 0; $axis < 3; $axis ++ ) {
					if ( $this->moons[ $i ]['pos'][ $axis ] < $this->moons[ $j ]['pos'][ $axis ] ) {
						$this->moons[ $i ]['vel'][ $axis ] += 1;
						$this->moons[ $j ]['vel'][ $axis ] -= 1;
					} elseif ( $this->moons[ $i ]['pos'][ $axis ] > $this->moons[ $j ]['pos'][ $axis ] ) {
						$this->moons[ $i ]['vel'][ $axis ] -= 1;
						$this->moons[ $j ]['vel'][ $axis ] += 1;
					}
				}
			}
		}
	}

	/**
	 * Applies velocity to adjust the positions of moons.
	 */
	private function apply_velocity(): void {
		foreach ( $this->moons as &$moon ) {
			for ( $axis = 0; $axis < 3; $axis ++ ) {
				$moon['pos'][ $axis ] += $moon['vel'][ $axis ];
			}
		}
	}

	/**
	 * Calculates the total energy in the system.
	 *
	 * @return integer
	 */
	private function calculate_total_energy(): int {
		$total_energy = 0;

		foreach ( $this->moons as $moon ) {
			$potential_energy = array_sum( array_map( 'abs', $moon['pos'] ) );
			$kinetic_energy   = array_sum( array_map( 'abs', $moon['vel'] ) );
			$total_energy     += $potential_energy * $kinetic_energy;
		}

		return $total_energy;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-12-test.txt' : '/data/day-12.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$data = [];

		foreach ( $lines as $line ) {
			// Parses the line and extract relevant data
			// Example: <x=1, y=2, z=3> becomes [ 1, 2, 3 ]
			$data[] = array_map( 'intval', preg_match_all( '/-?\d+/', $line, $matches ) ? $matches[0] : [] );
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
	$day12  = new Day12( $test, $part );
	$result = $day12->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 1940,
			'real' => 14809,
		],
		2 => [
			'test' => 4686774924,
			'real' => 282270365571288,
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
