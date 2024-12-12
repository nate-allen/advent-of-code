<?php

namespace AdventOfCode\Year2024;

/**
 * Day 12: Garden Groups
 */
class Day12 {
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
	 * @var array<string>
	 */
	private array $data;

	/**
	 * Directions for navigating the map.
	 */
	private array $directions = [
		[ - 1, 0 ], // NORTH
		[ 0, 1 ],  // EAST
		[ 1, 0 ],  // SOUTH
		[ 0, - 1 ], // WEST
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
	 * Part 1: What is the total price of fencing all regions on the map?
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$map       = array_map( 'str_split', $this->data );
		$visited   = [];
		$total     = 0;

		foreach ( $map as $r => $row ) {
			foreach ( array_keys( $row ) as $c ) {
				// Skip cells that are already visited
				if ( isset( $visited["$r-$c"] ) ) {
					continue;
				}

				$fences = [];
				[ $area, $perimeter ] = $this->find_region( $map, $r, $c, $fences, $visited );
				$total += ( $area * $perimeter );
			}
		}

		return $total;
	}

	/**
	 * Part 2: Count the number of fence segments are required for all regions and multiply the count of these fence
	 * segments by the region area.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$map     = array_map( 'str_split', $this->data );
		$visited = [];
		$total   = 0;

		foreach ( $map as $r => $row ) {
			foreach ( array_keys( $row ) as $c ) {
				if ( isset( $visited["$r-$c"] ) ) {
					continue;
				}

				$fences = [];
				[ $area, $perimeter ] = $this->find_region( $map, $r, $c, $fences, $visited );

				// Count all fence segments in each direction
				$sides = 0;

				// North and south segments
				for ( $i = 0; $i < count( $map ); $i ++ ) {
					// Count north side segments
					for ( $j = 0; $j < count( $map[0] ); $j ++ ) {
						if ( isset( $fences["$i-$j"] ) && $fences["$i-$j"][0] ) {
							$sides += 1;
							while ( isset( $fences[ "$i-" . ( $j + 1 ) ] ) && $fences[ "$i-" . ( $j + 1 ) ][0] ) {
								$j ++;
							}
						}
					}

					// Count south side segments
					for ( $j = 0; $j < count( $map[0] ); $j ++ ) {
						if ( isset( $fences["$i-$j"] ) && $fences["$i-$j"][2] ) {
							$sides += 1;
							while ( isset( $fences[ "$i-" . ( $j + 1 ) ] ) && $fences[ "$i-" . ( $j + 1 ) ][2] ) {
								$j ++;
							}
						}
					}
				}

				// East and west segments
				for ( $i = 0; $i < count( $map ); $i ++ ) {
					// Count east side segments
					for ( $j = 0; $j < count( $map ); $j ++ ) {
						if ( isset( $fences["$j-$i"] ) && $fences["$j-$i"][1] ) {
							$sides += 1;
							while ( isset( $fences[ "$j-" . $i ] ) && $fences["$j-$i"][1] ) {
								$j ++;
							}
						}
					}

					// Count west segments
					for ( $j = 0; $j < count( $map ); $j ++ ) {
						if ( isset( $fences["$j-$i"] ) && $fences["$j-$i"][3] ) {
							$sides += 1;
							while ( isset( $fences["$j-$i"] ) && $fences["$j-$i"][3] ) {
								$j ++;
							}
						}
					}
				}

				$total += $sides * $area;
			}
		}

		return $total;
	}

	/**
	 * Recursively calculates the area and perimeter of a connected region.
	 *
	 * @param array   $grid    The grid representing the region map.
	 * @param integer $row     The current row index.
	 * @param integer $col     The current column index.
	 * @param array   $fences  Tracks boundaries for each cell in the region.
	 * @param array   $visited Tracks visited cells.
	 *
	 * @return array
	 */
	private function find_region( array $grid, int $row, int $col, array &$fences, array &$visited ): array {
		// Mark the current cell as visited
		$visited["$row-$col"] = true;

		$area      = 1;
		$perimeter = 0;

		// Initialize fences for the current cell if not already set
		if ( ! isset( $fences["$row-$col"] ) ) {
			$fences["$row-$col"] = [ false, false, false, false ];
		}

		// Traverse all possible directions
		foreach ( $this->directions as $d => $dir ) {
			$rd = $row + $dir[0];
			$cd = $col + $dir[1];

			// Check if the adjacent cell is out of bounds or part of another region.
			if (
				$rd < 0 || $cd < 0 ||
				$rd >= count( $grid ) || $cd >= count( $grid[ $row ] ) ||
				$grid[ $rd ][ $cd ] !== $grid[ $row ][ $col ]
			) {
				$perimeter                 += 1;
				$fences["$row-$col"][ $d ] = true;
			} elseif ( ! isset( $visited["$rd-$cd"] ) ) {
				// Recursively explore the connected region.
				[ $a, $p ] = $this->find_region( $grid, $rd, $cd, $fences, $visited );
				$area      += $a;
				$perimeter += $p;
			}
		}

		return [ $area, $perimeter ];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array<string>
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-12-test.txt' : '/data/day-12.txt';
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
	$day12  = new Day12( $test, $part );
	$result = $day12->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 1930,
			'real' => 1363682,
		],
		2 => [
			'test' => 1206,
			'real' => 787680,
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
