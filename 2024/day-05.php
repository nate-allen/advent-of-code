<?php

namespace AdventOfCode\Year2024;

/**
 * Day 05: Print Queue
 */
class Day05 {
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
	 * Part 1: Add up the middle page number from correctly-ordered updates.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$dependencies  = $this->build_dependency_map( $this->data['rules'] );
		$valid_updates = [];

		foreach ( $this->data['updates'] as $update ) {
			if ( $this->is_valid_update( $dependencies, $update ) ) {
				$valid_updates[] = $update;
			}
		}

		$middle_sum = 0;

		foreach ( $valid_updates as $update ) {
			$middle_position  = intval( count( $update ) / 2 );
			$middle_sum   += $update[ $middle_position ];
		}

		return $middle_sum;
	}

	/**
	 * Part 2: Add up the middle page numbers after correctly ordering invalid updates.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$dependencies = $this->build_dependency_map( $this->data['rules'] );

		// Get all updates that are invalid
		$invalid_updates = [];
		foreach ( $this->data['updates'] as $update ) {
			if ( ! $this->is_valid_update( $dependencies, $update ) ) {
				$invalid_updates[] = $update;
			}
		}

		$middle_sum = 0;
		foreach ( $invalid_updates as $invalid_update ) {
			$corrected_update = $this->reorder_update( $dependencies, $invalid_update );
			$middle_position  = intval( count( $corrected_update ) / 2 );
			$middle_sum       += $corrected_update[ $middle_position ];
		}

		return $middle_sum;
	}

	/**
	 * Builds a map from the rules.
	 *
	 * Keys are page numbers, values are arrays of pages that must come after the key.
	 *
	 * @param array $rules
	 * @return array
	 */
	private function build_dependency_map(array $rules): array {
		$map = [];

		foreach ( $rules as $rule ) {
			$map[ $rule[0] ][] = $rule[1];
		}

		return $map;
	}

	/**
	 * Validates an update against the rules.
	 *
	 * @param array $dependency_map The dependency rules.
	 * @param array $update         The update to validate.
	 *
	 * @return bool
	 */
	private function is_valid_update( array $dependency_map, array $update ): bool {
		// Keys are page numbers, values are their positions
		$page_positions = array_flip( $update );

		foreach ( $dependency_map as $page => $dependents ) {
			foreach ( $dependents as $dependent ) {
				// Check if both page and dependent exist in the current update.
				if ( isset( $page_positions[ $page ], $page_positions[ $dependent ] ) ) {
					// If page's position is greater than dependent's position, it's not valid.
					if ( $page_positions[ $page ] > $page_positions[ $dependent ] ) {
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Reorders an update to based on the dependency rules.
	 *
	 * @param array $dependencies The dependency map.
	 * @param array $update       The current list of pages in the update to reorder.
	 *
	 * @return array
	 */
	private function reorder_update( array $dependencies, array $update ): array {
		$positions = array_flip( $update );

		// Keep trying until no changes are needed
		$changed = true;

		while ( $changed ) {
			$changed = false;

			// Iterate over each dependency (x must come before y)
			foreach ( $dependencies as $x => $dependents ) {
				foreach ( $dependents as $y ) {
					// If both x and y are in the update and x comes after y, swap them
					if ( isset( $positions[ $x ], $positions[ $y ] ) && $positions[ $x ] > $positions[ $y ] ) {
						// Swap x and y in the update
						$x_index = $positions[ $x ];
						$y_index = $positions[ $y ];

						// Perform the swap in the update array
						$update[ $x_index ] = $y;
						$update[ $y_index ] = $x;

						// Update positions map
						$positions[ $x ] = $y_index;
						$positions[ $y ] = $x_index;

						$changed = true;
					}
				}
			}
		}

		return $update;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data(bool $test): array {
		$file    = $test ? '/data/day-05-test.txt' : '/data/day-05.txt';
		$content = trim( file_get_contents( __DIR__ . $file ) );

		// Split the content into rules and updates sections
		[ $rules_section, $updates_section ] = explode( "\n\n", $content );

		// Parse rules into a list of directed edges
		$rules = array_map( fn( $rule ) => array_map( 'intval', explode( '|', trim( $rule ) ) ), explode( "\n", $rules_section ) );

		// Parse updates into an array of page number arrays
		$updates = array_map(
			fn( $line ) => array_map( 'intval', explode( ',', trim( $line ) ) ),
			explode( "\n", $updates_section )
		);

		return [
			'rules'   => $rules,
			'updates' => $updates,
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
	$day05  = new Day05( $test, $part );
	$result = $day05->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 143,
			'real' => 6051,
		],
		2 => [
			'test' => 123,
			'real' => 5093,
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
