<?php

namespace AdventOfCode\Year2018;

/**
 * Day 07: The Sum of Its Parts
 */
class Day07 {
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
	 * @return int|string
	 */
	public function run(): int|string {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Determine the order in which steps should be completed using topological sort.
	 *
	 * @return string The order of steps as a string (e.g., "CABDFE")
	 */
	private function solve_part_1(): string {
		// Build dependency graph
		$graph      = $this->build_dependency_graph();
		$in_degree  = $graph['in_degree'];
		$dependents = $graph['dependents'];
		$available  = $graph['available'];

		$completed = '';

		// Process steps using Kahn's algorithm
		while ( ! empty( $available ) ) {
			// Take the first (alphabetically) available step
			$current    = array_shift( $available );
			$completed .= $current;

			// Update dependents: decrement their in-degree
			if ( isset( $dependents[$current] ) ) {
				foreach ( $dependents[$current] as $dependent ) {
					$in_degree[ $dependent ]--;
					// If in-degree reaches 0, add to available
					if ( $in_degree[ $dependent ] === 0 ) {
						$available[] = $dependent;
					}
				}
			}

			// Keep available array sorted alphabetically
			sort( $available );
		}

		return $completed;
	}

	/**
	 * Part 2: Calculate the total time needed to complete all steps with multiple workers.
	 *
	 * @return int The total time in seconds to complete all steps
	 */
	private function solve_part_2(): int {
		// Configuration: 2 workers for test, 5 for real; base_time 0 for test, 60 for real
		$num_workers = $this->is_test ? 2 : 5;
		$base_time   = $this->is_test ? 0 : 60;

		// Build dependency graph
		$graph      = $this->build_dependency_graph();
		$in_degree  = $graph['in_degree'];
		$dependents = $graph['dependents'];
		$available  = $graph['available'];

		// Simulation state
		$current_time = 0;
		$active_work  = []; // Maps step => completion_time

		// Main simulation loop
		while ( ! empty( $active_work ) || ! empty( $available ) ) {
			// Phase 1: Complete finished steps
			if ( ! empty( $active_work ) ) {
				// Find minimum completion time (next event)
				$next_completion = min( $active_work );
				$current_time    = $next_completion;

				// Find all steps that complete at this time
				$completed_steps = [];
				foreach ( $active_work as $step => $completion_time ) {
					if ( $completion_time === $current_time ) {
						$completed_steps[] = $step;
						unset( $active_work[ $step ] );
					}
				}

				// Process each completed step
				foreach ( $completed_steps as $completed_step ) {
					// Update dependents: decrement their in-degree
					if ( isset( $dependents[$completed_step] ) ) {
						foreach ( $dependents[$completed_step] as $dependent ) {
							$in_degree[ $dependent ]--;
							// If in-degree reaches 0, add to available
							if ( $in_degree[ $dependent ] === 0 ) {
								$available[] = $dependent;
							}
						}
					}
				}
			}

			// Phase 2: Assign new work to idle workers
			sort( $available ); // Maintain alphabetical order
			$idle_workers = $num_workers - count( $active_work );

			while ( $idle_workers > 0 && ! empty( $available ) ) {
				// Take the first (alphabetically) available step
				$next_step = array_shift( $available );

				// Calculate completion time
				$duration        = $this->get_step_duration( $next_step, $base_time );
				$completion_time = $current_time + $duration;

				// Assign to a worker
				$active_work[ $next_step ] = $completion_time;
				$idle_workers--;
			}
		}

		return $current_time;
	}

	/**
	 * Builds the dependency graph from the parsed input data.
	 *
	 * @return array An array containing:
	 *               - 'in_degree': Maps each step to its number of prerequisites
	 *               - 'dependents': Maps each prerequisite to steps that depend on it
	 *               - 'available': Array of steps with 0 in-degree, sorted alphabetically
	 */
	private function build_dependency_graph(): array {
		$in_degree  = []; // Number of prerequisites for each step
		$dependents = []; // Steps that depend on each prerequisite
		$all_steps  = []; // All unique steps found

		// Initialize graph from parsed dependencies
		foreach ( $this->data as $dep ) {
			$prereq = $dep['prerequisite'];
			$step   = $dep['step'];

			// Track all steps
			$all_steps[ $prereq ] = true;
			$all_steps[ $step ]   = true;

			// Initialize in-degree if not set
			if ( ! isset( $in_degree[ $step ] ) ) {
				$in_degree[ $step ] = 0;
			}
			if ( ! isset( $in_degree[ $prereq ] ) ) {
				$in_degree[ $prereq ] = 0;
			}

			// Increment in-degree for the dependent step
			$in_degree[ $step ]++;

			// Track dependents for efficient updates
			if ( ! isset( $dependents[ $prereq ] ) ) {
				$dependents[ $prereq ] = [];
			}
			$dependents[ $prereq ][] = $step;
		}

		// Find all steps with 0 in-degree (available to start)
		$available = [];
		foreach ( $all_steps as $step => $_ ) {
			if ( ($in_degree[$step] ?? 0) === 0 ) {
				$available[] = $step;
			}
		}

		// Sort available steps alphabetically
		sort( $available );

		return [
			'in_degree'  => $in_degree,
			'dependents' => $dependents,
			'available'  => $available,
		];
	}

	/**
	 * Calculates the duration of a step based on its letter and base time.
	 *
	 * @param string $step The step letter (A-Z).
	 * @param int    $base_time The base time to add (0 for test, 60 for real).
	 *
	 * @return int The duration in seconds.
	 */
	private function get_step_duration( string $step, int $base_time ): int {
		$letter_value = ord( $step ) - ord( 'A' ) + 1;
		return $base_time + $letter_value;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of dependency pairs, each with 'prerequisite' and 'step' keys
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-07-test.txt' : '/data/day-07.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$dependencies = [];
		foreach ( $lines as $line ) {
			// Parse: "Step X must be finished before step Y can begin."
			if ( preg_match( '/Step ([A-Z]) must be finished before step ([A-Z]) can begin\./', $line, $matches ) ) {
				$dependencies[] = [
					'prerequisite' => $matches[1],
					'step'         => $matches[2],
				];
			}
		}

		return $dependencies;
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
	$day07  = new Day07( $test, $part );
	$result = $day07->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 'CABDFE',
			'real' => 'EUGJKYFQSCLTWXNIZMAPVORDBH',
		],
		2 => [
			'test' => 15,
			'real' => 1014,
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
