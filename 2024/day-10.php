<?php

namespace AdventOfCode\Year2024;

/**
 * Day 10: Hoof It
 */
class Day10 {
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

	/**
	 * Number of rows in the map.
	 *
	 * @var integer
	 */
	private int $rows;

	/**
	 * Number of columns in the map.
	 *
	 * @var integer
	 */
	private int $cols;

	/**
	 * Cache to store the number of paths from a specific position.
	 *
	 * @var array
	 */
	private array $cache = [];

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
		$this->rows    = count( $this->data );
		$this->cols    = count( $this->data[0] );
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
	 * Part 1: Calculate the sum of the scores of all trailheads on the topographic map.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$total_score = 0;

		foreach ( $this->get_trailheads() as $trailhead ) {
			$total_score += $this->count_reachable_nines( $trailhead[0], $trailhead[1], $this->data );
		}

		return $total_score;
	}


	/**
	 * Part 2: Calculate the sum of the ratings of all trailheads on the topographic map.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$total_rating = 0;

		foreach ( $this->get_trailheads() as $trailhead ) {
			$total_rating += $this->count_paths( $trailhead[0], $trailhead[1], $this->data );
		}

		return $total_rating;
	}

	/**
	 * Counts paths from a specific position
	 *
	 * @param integer $x   Current row position
	 * @param integer $y   Current column position
	 * @param array   $map Topographic map
	 *
	 * @return int
	 */
	private function count_paths( int $x, int $y, array $map ): int {
		// If the current position 9
		if ( $map[ $x ][ $y ] === 9 ) {
			return 1;
		}

		// Use cached result if it has been calculated before
		if ( isset( $this->cache["$x,$y"] ) ) {
			return $this->cache["$x,$y"];
		}

		$total_paths = 0;

		foreach ( $this->directions as [$dx, $dy] ) {
			$nx = $x + $dx;
			$ny = $y + $dy;

			// Skip if the position is out of bounds
			if ( $nx < 0 || $nx >= $this->rows || $ny < 0 || $ny >= $this->cols ) {
				continue;
			}

			// Skip if the hiking trail rule is invalid
			if ( $map[ $nx ][ $ny ] !== $map[ $x ][ $y ] + 1 ) {
				continue;
			}

			$total_paths += $this->count_paths( $nx, $ny, $map );
		}

		// Cache the result
		$this->cache["$x,$y"] = $total_paths;

		return $total_paths;
	}

	/**
	 * Counts the number of reachable height 9 positions from a specific trailhead.
	 *
	 * @param int $startX Starting row position
	 * @param int $startY Starting column position
	 * @param array $map Topographic map
	 *
	 * @return int
	 */
	private function count_reachable_nines( int $startX, int $startY, array $map ): int {
		$queue           = [ [ $startX, $startY ] ];
		$visited         = [];
		$reachable_nines = [];

		while ( ! empty( $queue ) ) {
			[ $x, $y ] = array_shift( $queue );

			foreach ( $this->directions as [$dx, $dy] ) {
				$nx = $x + $dx;
				$ny = $y + $dy;

				// Skip if the position is out of bounds
				if ( $nx < 0 || $nx >= $this->rows || $ny < 0 || $ny >= $this->cols ) {
					continue;
				}

				// Skip if already visited
				if ( isset( $visited["$nx,$ny"] ) ) {
					continue;
				}

				// Skip if the hiking trail rule is invalid
				if ( $map[ $nx ][ $ny ] !== $map[ $x ][ $y ] + 1 ) {
					continue;
				}

				// Mark position as visited
				$visited["$nx,$ny"] = true;

				// If we reach 9, add it to reachable positions
				if ( $map[ $nx ][ $ny ] === 9 ) {
					$reachable_nines["$nx,$ny"] = true;
				}

				// Add position to queue
				$queue[] = [ $nx, $ny ];
			}
		}

		return count( $reachable_nines );
	}

	/**
	 * Helper function to get all positions with a height of 0.
	 *
	 * @return array
	 */
	private function get_trailheads(): array {
		$trailheads = [];

		for ( $row = 0; $row < $this->rows; $row ++ ) {
			for ( $column = 0; $column < $this->cols; $column ++ ) {
				if ( $this->data[ $row ][ $column ] === 0 ) {
					$trailheads[] = [ $row, $column ];
				}
			}
		}

		return $trailheads;
	}


	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-10-test.txt' : '/data/day-10.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( fn( $line ) => array_map( 'intval', str_split( trim( $line ) ) ), $lines );
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
	$day10  = new Day10( $test, $part );
	$result = $day10->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 36,
			'real' => 593,
		],
		2 => [
			'test' => 81,
			'real' => 1192,
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
