<?php

namespace AdventOfCode\Year2021;

/**
 * Day 15: Chiton
 */
class Day15 {
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

	/**
	 * Movement directions (up, down, left, right).
	 *
	 * @var array
	 */
	private array $directions = [
		[ - 1, 0 ],
		[ 1, 0 ],
		[ 0, - 1 ],
		[ 0, 1 ],
	];

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
	 * Part 1: Find the lowest risk path in the cave.
	 *
	 * The cavern is represented as a grid of risk levels. The goal is to move from the top-left corner to the
	 * bottom-right corner while minimizing the total risk. Each movement adds the risk level of the cell you enter.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$grid = $this->data;

		return $this->find_lowest_risk_path( $grid );
	}

	/**
	 * Part 2: Find the lowest risk path in an expanded cave.
	 *
	 * The cave grid is expanded to be 5 times larger in both dimensions, with each subsequent tile increasing the
	 * risk level by 1 (wrapping from 9 to 1). The goal is to move from the top-left corner to the bottom-right corner
	 * while minimizing the total risk.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		// Expand the grid by 5 times in both dimensions
		$grid = $this->expand_grid($this->data);

		return $this->find_lowest_risk_path( $grid );
	}

	/**
	 * Finds the lowest risk path from the top-left to the bottom-right of the grid using Dijkstra's algorithm.
	 *
	 * @param array $grid The risk level grid.
	 *
	 * @return integer
	 */
	private function find_lowest_risk_path( array $grid ): int {
		$row_count = count( $grid );
		$col_count = count( $grid[0] );

		// Initialize the risks array with maximum values.
		$risks       = array_fill( 0, $row_count, array_fill( 0, $col_count, PHP_INT_MAX ) );
		$risks[0][0] = 0;

		// Priority queue to explore grid cells in order of risk.
		$queue = new \SplPriorityQueue();
		$queue->insert( [ 0, 0 ], 0 ); // [[row, col], risk]

		while ( ! $queue->isEmpty() ) {
			[ $current_row, $current_col ] = $queue->extract();

			foreach ( $this->directions as [$delta_row, $delta_col] ) {
				$next_row = $current_row + $delta_row;
				$next_col = $current_col + $delta_col;

				// Check if the next cell is within bounds.
				if ( $next_row >= 0 && $next_row < $row_count && $next_col >= 0 && $next_col < $col_count ) {
					$new_risk = $risks[ $current_row ][ $current_col ] + $grid[ $next_row ][ $next_col ];

					// Update the risk if the new risk is lower.
					if ( $new_risk < $risks[ $next_row ][ $next_col ] ) {
						$risks[ $next_row ][ $next_col ] = $new_risk;
						$queue->insert( [ $next_row, $next_col ], - $new_risk );
					}
				}
			}
		}

		return $risks[ $row_count - 1 ][ $col_count - 1 ];
	}

	/**
	 * Expands the grid to a 5x5 larger version by incrementing risk levels and wrapping values greater than 9 back to 1.
	 *
	 * @param array $grid The original risk level grid.
	 *
	 * @return array
	 */
	private function expand_grid( array $grid ): array {
		$rows     = count( $grid );
		$cols     = count( $grid[0] );
		$expanded = array_fill( 0, $rows * 5, array_fill( 0, $cols * 5, 0 ) );

		for ( $tile_row = 0; $tile_row < 5; $tile_row ++ ) {
			for ( $rile_col = 0; $rile_col < 5; $rile_col ++ ) {
				for ( $row = 0; $row < $rows; $row ++ ) {
					for ( $col = 0; $col < $cols; $col ++ ) {
						// Calculate the new risk level.
						$new_risk = $grid[ $row ][ $col ] + $tile_row + $rile_col;

						// If the risk level is greater than 9, wrap it back to 1.
						if ( $new_risk > 9 ) {
							$new_risk -= 9;
						}

						// Assign the new risk to the expanded grid.
						$expanded[ $row + $tile_row * $rows ][ $col + $rile_col * $cols ] = $new_risk;
					}
				}
			}
		}

		return $expanded;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-15-test.txt' : '/data/day-15.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( 'str_split', $lines );
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
	$day15  = new Day15( $test, $part );
	$result = $day15->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 40,
			'real' => 685,
		],
		2 => [
			'test' => 315,
			'real' => 2995,
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
