<?php

namespace AdventOfCode\Year2019;

/**
 * Day 24: Planet of Discord
 */
class Day24 {
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
	 * Part 1: Simulate the evolution of the 5x5 grid until a layout repeats. Then compute and return the biodiversity
	 * rating for the first repeating layout.
	 *
	 * Rules:
	 * - A bug dies unless there is exactly one bug adjacent to it.
	 * - An empty space becomes infested with a bug if exactly one or two bugs are adjacent.
	 * - Otherwise, the tile remains the same.
	 *
	 * Biodiversity rating is calculated by assigning a value to each tile
	 * (from top-left to bottom-right, using powers of two) and summing the values
	 * for each tile containing a bug.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$grid = $this->data;

		// Track seen layouts
		$seen_layouts = [];

		// Loop until we find a duplicate layout
		while ( true ) {
			// Create a single string representing the current layout
			$layout = implode( '', $grid );

			// If we have seen this layout before, break out of the loop
			if ( isset( $seen_layouts[ $layout ] ) ) {
				break;
			}

			// Mark this layout as seen
			$seen_layouts[ $layout ] = true;

			$new_grid = [];

			// Process each cell in the grid
			for ( $row = 0; $row < count( $grid ); $row ++ ) {
				$new_row = '';
				// Get current row as string
				$current_row = $grid[ $row ];
				$max_col     = strlen( $current_row );

				for ( $col = 0; $col < $max_col; $col ++ ) {
					$adjacent = 0;
					// Check cell above
					if ( $row > 0 && $grid[ $row - 1 ][ $col ] === '#' ) {
						$adjacent ++;
					}

					// Check cell below
					if ( $row < count( $grid ) - 1 && $grid[ $row + 1 ][ $col ] === '#' ) {
						$adjacent ++;
					}

					// Check cell to the left
					if ( $col > 0 && $current_row[ $col - 1 ] === '#' ) {
						$adjacent ++;
					}

					// Check cell to the right
					if ( $col < $max_col - 1 && $current_row[ $col + 1 ] === '#' ) {
						$adjacent ++;
					}

					// Apply the evolution rules
					if ( $current_row[ $col ] === '#' && $adjacent !== 1 ) {
						$new_row .= '.';
					} elseif ( $current_row[ $col ] === '.' && ( $adjacent === 1 || $adjacent === 2 ) ) {
						$new_row .= '#';
					} else {
						$new_row .= $current_row[ $col ];
					}
				}
				$new_grid[] = $new_row;
			}

			// Update the grid with the new state
			$grid = $new_grid;
		}

		// Once a duplicate layout is found, compute its biodiversity rating.
		// Each tile (in reading order) is worth 2^(tile index) if it contains a bug.
		$biodiversity = 0;
		$index        = 0;
		foreach ( $grid as $row_string ) {
			$max_col = strlen( $row_string );
			for ( $col = 0; $col < $max_col; $col ++ ) {
				if ( $row_string[ $col ] === '#' ) {
					$biodiversity += ( 1 << $index ); // equivalent to 2^$index
				}
				$index ++;
			}
		}

		return $biodiversity;
	}

	/**
	 * Part 2: Simulate recursive grids over 200 minutes.
	 *
	 * Each level is a 5x5 grid (with the center cell always treated as a hole). After 200 minutes, the total number of
	 * bugs (across all levels) is returned.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$levels = [];
		$grid   = [];

		foreach ( $this->data as $y => $line ) {
			$row = str_split( $line );

			// Force the center cell (2,2) to be a hole.
			if ( $y === 2 ) {
				$row[2] = '?';
			}

			$grid[] = implode( '', $row );
		}
		$levels[0] = $grid;

		// Simulate for 200 minutes.
		for ( $minute = 0; $minute < 200; $minute ++ ) {
			// Determine current min and max levels.
			$min_level  = min( array_keys( $levels ) );
			$max_level  = max( array_keys( $levels ) );
			$new_levels = [];

			// Process levels from one below min_level to one above max_level.
			for ( $level = $min_level - 1; $level <= $max_level + 1; $level ++ ) {
				$new_grid = [];

				for ( $y = 0; $y < 5; $y ++ ) {
					$new_row = '';

					for ( $x = 0; $x < 5; $x ++ ) {
						// The center cell is always a hole.
						if ( $x === 2 && $y === 2 ) {
							$new_row .= '?';
							continue;
						}

						$bug_count = 0;
						$directions = [
							[ 0, - 1 ],
							[ 0, 1 ],
							[ - 1, 0 ],
							[ 1, 0 ]
						];

						foreach ( $directions as $dir ) {
							$dx = $dir[0];
							$dy = $dir[1];
							$nx = $x + $dx;
							$ny = $y + $dy;

							// Case 1: Neighbor would be the center.
							if ( $nx === 2 && $ny === 2 ) {
								$inner_level = $level + 1;
								if ( isset( $levels[ $inner_level ] ) ) {

									// Depending on direction, count the corresponding edge in the inner level.
									if ( $dx === 0 && $dy === - 1 ) {

										// Moving up into center: count the bottom row.
										for ( $ix = 0; $ix < 5; $ix ++ ) {
											if ( $levels[ $inner_level ][4][ $ix ] === '#' ) {
												$bug_count ++;
											}
										}
									} elseif ( $dx === 0 && $dy === 1 ) {
										// Moving down into center: count the top row.
										for ( $ix = 0; $ix < 5; $ix ++ ) {
											if ( $levels[ $inner_level ][0][ $ix ] === '#' ) {
												$bug_count ++;
											}
										}
									} elseif ( $dx === - 1 && $dy === 0 ) {
										// Moving left into center: count the right column.
										for ( $iy = 0; $iy < 5; $iy ++ ) {
											if ( $levels[ $inner_level ][ $iy ][4] === '#' ) {
												$bug_count ++;
											}
										}
									} elseif ( $dx === 1 && $dy === 0 ) {
										// Moving right into center: count the left column.
										for ( $iy = 0; $iy < 5; $iy ++ ) {
											if ( $levels[ $inner_level ][ $iy ][0] === '#' ) {
												$bug_count ++;
											}
										}
									}
								}
							} // Case 2: Neighbor is out of bounds (so use the outer level).
							elseif ( $nx < 0 || $nx > 4 || $ny < 0 || $ny > 4 ) {
								$outer_level = $level - 1;
								if ( isset( $levels[ $outer_level ] ) ) {
									if ( $nx < 0 ) {
										// Left edge: corresponds to cell (1,2) in outer level.
										if ( $levels[ $outer_level ][2][1] === '#' ) {
											$bug_count ++;
										}
									} elseif ( $nx > 4 ) {
										// Right edge: corresponds to cell (3,2).
										if ( $levels[ $outer_level ][2][3] === '#' ) {
											$bug_count ++;
										}
									} elseif ( $ny < 0 ) {
										// Top edge: corresponds to cell (2,1).
										if ( $levels[ $outer_level ][1][2] === '#' ) {
											$bug_count ++;
										}
									} elseif ( $ny > 4 ) {
										// Bottom edge: corresponds to cell (2,3).
										if ( $levels[ $outer_level ][3][2] === '#' ) {
											$bug_count ++;
										}
									}
								}
							} // Case 3: Neighbor is within the same level.
							else {
								if ( isset( $levels[ $level ] ) && $levels[ $level ][ $ny ][ $nx ] === '#' ) {
									$bug_count ++;
								}
							}
						}

						$current_cell = isset( $levels[ $level ] ) ? $levels[ $level ][ $y ][ $x ] : '.';
						if ( $current_cell === '#' && $bug_count !== 1 ) {
							$new_row .= '.';
						} elseif ( $current_cell === '.' && ( $bug_count === 1 || $bug_count === 2 ) ) {
							$new_row .= '#';
						} else {
							$new_row .= $current_cell;
						}
					}
					$new_grid[] = $new_row;
				}
				$new_levels[ $level ] = $new_grid;
			}
			$levels = $new_levels;
		}

		// Count the total bugs (ignoring the center hole in every level).
		$total_bugs = 0;
		foreach ( $levels as $grid ) {
			for ( $y = 0; $y < 5; $y ++ ) {
				for ( $x = 0; $x < 5; $x ++ ) {
					if ( $x === 2 && $y === 2 ) {
						continue;
					}
					if ( $grid[ $y ][ $x ] === '#' ) {
						$total_bugs ++;
					}
				}
			}
		}

		return $total_bugs;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-24-test.txt' : '/data/day-24.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return $lines;
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
	$day24  = new Day24( $test, $part );
	$result = $day24->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 2129920,
			'real' => 11042850
		],
		2 => [
			'test' => 1922,
			'real' => 1967,
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
