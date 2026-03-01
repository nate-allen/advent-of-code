<?php

namespace AdventOfCode\Year2018;

/**
 * Day 18: Settlers of The North Pole
 */
class Day18 {
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
	 * Part 1: Calculate resource value after 10 minutes
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$grid = $this->data;
		
		// Run simulation for 10 minutes
		for ( $minute = 0; $minute < 10; $minute++ ) {
			$grid = $this->simulate_minute( $grid );
		}
		
		return $this->count_resources( $grid );
	}

	/**
	 * Part 2: Calculate resource value after 1 billion minutes using cycle detection
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$grid           = $this->data;
		$target_minutes = 1000000000;
		$seen_states    = [];
		$minute         = 0;

		// Store initial state
		$seen_states[ $this->grid_to_string( $grid ) ] = $minute;

		// Simulate until we find a cycle
		while ( $minute < $target_minutes ) {
			// Simulate one minute
			$grid = $this->simulate_minute( $grid );
			$minute++;

			$state_key = $this->grid_to_string( $grid );

			// Check if we've seen this state before
			if ( isset( $seen_states[ $state_key ] ) ) {
				// Found a cycle!
				$cycle_start   = $seen_states[ $state_key ];
				$cycle_length  = $minute - $cycle_start;
				$remaining     = $target_minutes - $minute;
				$remaining_mod = $remaining % $cycle_length;

				// Simulate the remaining iterations to reach target_minutes
				for ( $i = 0; $i < $remaining_mod; $i++ ) {
					$grid = $this->simulate_minute( $grid );
				}

				return $this->count_resources( $grid );
			}

			// Store this state
			$seen_states[ $state_key ] = $minute;
		}

		// If we didn't find a cycle (shouldn't happen), return the result
		return $this->count_resources( $grid );
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

	/**
	 * Simulates one minute of the cellular automaton.
	 *
	 * @param array $grid Current grid state (array of strings).
	 *
	 * @return array New grid state after one minute.
	 */
	private function simulate_minute( array $grid ): array {
		$rows     = count( $grid );
		$cols     = strlen( $grid[0] );
		$new_grid = [];

		for ( $r = 0; $r < $rows; $r++ ) {
			$new_row = '';
			for ( $c = 0; $c < $cols; $c++ ) {
				$current     = $grid[ $r ][ $c ];
				$trees       = 0;
				$lumberyards = 0;

				// Count adjacent trees and lumberyards (8 neighbors)
				for ( $dr = -1; $dr <= 1; $dr++ ) {
					for ( $dc = -1; $dc <= 1; $dc++ ) {
						if ( $dr === 0 && $dc === 0 ) {
							continue; // Skip self
						}
						$nr = $r + $dr;
						$nc = $c + $dc;

						// Check bounds
						if ( $nr >= 0 && $nr < $rows && $nc >= 0 && $nc < $cols ) {
							$neighbor = $grid[ $nr ][ $nc ];
							if ( $neighbor === '|' ) {
								$trees++;
							} elseif ( $neighbor === '#' ) {
								$lumberyards++;
							}
						}
					}
				}

				// Apply transformation rules
				if ( $current === '.' ) {
					// Open → Trees if 3+ adjacent are trees
					$new_row .= ($trees >= 3) ? '|' : '.';
				} elseif ( $current === '|' ) {
					// Trees → Lumberyard if 3+ adjacent are lumberyards
					$new_row .= ($lumberyards >= 3) ? '#' : '|';
				} else { // $current === '#'
					// Lumberyard → Lumberyard if at least 1 adjacent lumberyard AND at least 1 adjacent tree
					$new_row .= ($lumberyards >= 1 && $trees >= 1) ? '#' : '.';
				}
			}
			$new_grid[] = $new_row;
		}

		return $new_grid;
	}

	/**
	 * Converts grid to a string representation for cycle detection.
	 *
	 * @param array $grid Grid state (array of strings).
	 *
	 * @return string String representation of the grid.
	 */
	private function grid_to_string( array $grid ): string {
		return implode( "\n", $grid );
	}

	/**
	 * Counts trees and lumberyards in the grid and returns their product.
	 *
	 * @param array $grid Grid state (array of strings).
	 *
	 * @return int Product of tree count and lumberyard count.
	 */
	private function count_resources( array $grid ): int {
		$trees       = 0;
		$lumberyards = 0;

		foreach ( $grid as $row ) {
			for ( $i = 0; $i < strlen( $row ); $i++ ) {
				if ( $row[ $i ] === '|' ) {
					$trees++;
				} elseif ( $row[ $i ] === '#' ) {
					$lumberyards++;
				}
			}
		}

		return $trees * $lumberyards;
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
	$day18  = new Day18( $test, $part );
	$result = $day18->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 1147,
			'real' => 360720,
		],
		2 => [
			'test' => 0,
			'real' => 197276,
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
