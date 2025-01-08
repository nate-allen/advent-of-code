<?php

namespace AdventOfCode\Year2021;

/**
 * Day 25: Sea Cucumber
 */
class Day25 {
	/**
	 * Whether to use the test data.
	 *
	 * @var bool
	 */
	private bool $is_test;

	/**
	 * Parsed sea floor grid.
	 *
	 * @var array
	 */
	private array $sea_floor;

	/**
	 * Number of rows in the grid.
	 *
	 * @var int
	 */
	private int $num_rows;

	/**
	 * Number of columns in the grid.
	 *
	 * @var int
	 */
	private int $num_cols;

	public function __construct( bool $test ) {
		$this->is_test  = $test;
		$this->sea_floor = $this->parse_data( $this->is_test );
		$this->num_rows = count( $this->sea_floor );
		$this->num_cols = count( $this->sea_floor[0] );
	}

	/**
	 * Put a description about what we have to do to complete this puzzle.
	 *
	 * @return integer
	 */
	public function run(): int {
		$steps = 0;

		// Keep simulating steps until no cucumbers move.
		while ( true ) {
			$steps ++;
			$moved = $this->simulate_step();

			if ( ! $moved ) {
				break;
			}
		}

		return $steps;
	}

	/**
	 * Simulates a single step of the sea cucumber movement.
	 *
	 * @return bool True if any sea cucumber moved, false otherwise.
	 */
	private function simulate_step(): bool {
		$moved = false;

		// Create a copy of the current sea floor state
		$next_sea_floor = $this->copy_grid( $this->sea_floor );

		// Move east-facing sea cucumbers ('>')
		for ( $row = 0; $row < $this->num_rows; $row ++ ) {
			for ( $col = 0; $col < $this->num_cols; $col ++ ) {
				// Skip if the current cell is not an east-facing sea cucumber.
				if ( $this->sea_floor[ $row ][ $col ] !== '>' ) {
					continue;
				}

				// Skip if the cell to the right is not empty.
				$next_col = ( $col + 1 ) % $this->num_cols;
				if ( $this->sea_floor[ $row ][ $next_col ] !== '.' ) {
					continue;
				}

				$moved = true;

				$next_sea_floor[ $row ][ $next_col ] = '>';
				$next_sea_floor[ $row ][ $col ]      = '.';
			}
		}

		// Update the sea floor for south-facing movement
		$final_sea_floor = $this->copy_grid( $next_sea_floor );

		// Move south-facing sea cucumbers ('v')
		for ( $row = 0; $row < $this->num_rows; $row ++ ) {
			for ( $col = 0; $col < $this->num_cols; $col ++ ) {
				// Skip if the current cell is not a south-facing sea cucumber.
				if ( $next_sea_floor[ $row ][ $col ] !== 'v' ) {
					continue;
				}

				// Skip if the cell below is not empty.
				$next_row = ( $row + 1 ) % $this->num_rows;
				if ( $next_sea_floor[ $next_row ][ $col ] !== '.' ) {
					continue;
				}

				$moved = true;

				$final_sea_floor[ $next_row ][ $col ] = 'v';
				$final_sea_floor[ $row ][ $col ]      = '.';
			}
		}

		// Update the sea floor to the final state after this step
		$this->sea_floor = $final_sea_floor;

		return $moved;
	}

	/**
	 * Creates a copy of the given grid.
	 *
	 * @param array $grid The grid to copy.
	 *
	 * @return array The copied grid.
	 */
	private function copy_grid( array $grid ): array {
		return array_map( fn( $row ) => $row, $grid );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file   = $test ? '/data/day-25-test.txt' : '/data/day-25.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( 'str_split', $lines );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param bool $test Whether to use test data.
 */
function run_part( bool $test ): void {
	$start  = microtime( true );
	$day25  = new Day25( $test );
	$result = $day25->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		'test' => 58,
		'real' => 598,
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $test ? $expected_values['test'] : $expected_values['real'] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for part and test mode
while ( true ) {
	$test = strtolower( trim( readline( 'Do you want to run the test? (y/n): ' ) ) );
	if ( in_array( $test, [ 'y', 'n' ], true ) ) {
		$test_mode = $test === 'y';
		run_part( $test_mode );
		break;
	}
	echo 'Invalid input. Please enter y or n.' . PHP_EOL;
}
