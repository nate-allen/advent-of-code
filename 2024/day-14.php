<?php

namespace AdventOfCode\Year2024;

/**
 * Day 14: Restroom Redoubt
 */
class Day14 {
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
	 * Part 1: Simulate robot movement for 100 seconds and calculate safety factor.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$width     = $this->is_test ? 11 : 101;
		$height    = $this->is_test ? 7 : 103;
		$quadrants = [ 0, 0, 0, 0 ];

		// Center of the grid.
		$mid_x = floor( $width / 2 );
		$mid_y = floor( $height / 2 );

		// Simulate positions after 100 seconds.
		foreach ( $this->data as $robot ) {
			// Calculate new position after 100 seconds.
			$x = ( $robot['p'][0] + 100 * $robot['v'][0] ) % $width;
			$y = ( $robot['p'][1] + 100 * $robot['v'][1] ) % $height;

			// Wrap around for negative positions.
			if ( $x < 0 ) {
				$x += $width;
			}
			if ( $y < 0 ) {
				$y += $height;
			}

			// Skip robots on the middle lines.
			if ( $x == $mid_x || $y == $mid_y ) {
				continue;
			}

			// Determine which quadrant the robot is in.
			if ( $x < $mid_x && $y < $mid_y ) {
				$quadrants[0] ++;
			} elseif ( $x > $mid_x && $y < $mid_y ) {
				$quadrants[1] ++;
			} elseif ( $x < $mid_x && $y > $mid_y ) {
				$quadrants[2] ++;
			} else {
				$quadrants[3] ++;
			}
		}

		// Calculate safety factor by multiplying the number of robots in each quadrant.
		return array_product( $quadrants );
	}

	/**
	 * Part 2: What is the fewest number of seconds that must elapse for the robots to display a Christmas Tree?
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$width  = $this->is_test ? 11 : 101;
		$height = $this->is_test ? 7 : 103;

		$time = 0;

		while ( true ) {
			// Track positions so we can check if they are unique.
			$positions = [];
			$unique    = true;

			foreach ( $this->data as $robot ) {
				// Update position for the current second.
				$x = ( $robot['p'][0] + $time * $robot['v'][0] ) % $width;
				$y = ( $robot['p'][1] + $time * $robot['v'][1] ) % $height;

				// Wrap around for negative positions.
				if ( $x < 0 ) {
					$x += $width;
				}
				if ( $y < 0 ) {
					$y += $height;
				}

				// Check if position is unique.
				if ( isset( $positions["$x,$y"] ) ) {
					$unique = false;
					break;
				}
				$positions["$x,$y"] = true;
			}

			// If all positions are unique, we found the answer.
			if ( $unique ) {
				// Output the grid to a file, so we can see what it looks like.
				// Only do this for the real data, since the test data is too small.
				if ( ! $this->is_test ) {
					$this->write_positions_to_file( $positions, $width, $height, $time );
				}

				return $time;
			}

			$time ++;
		}
	}

	/**
	 * Writes the grid showing the robots' positions to a file.
	 *
	 * @param array $positions Hash map of robot positions.
	 * @param int $width Width of the grid.
	 * @param int $height Height of the grid.
	 * @param int $time The time step at which the positions were recorded.
	 */
	private function write_positions_to_file( array $positions, int $width, int $height, int $time ): void {
		$filePath = __DIR__ . "/data/day-14-output.txt";

		// Create an empty grid.
		$grid = array_fill( 0, $height, str_repeat( '.', $width ) );
		foreach ( $positions as $key => $_ ) {
			[ $x, $y ] = explode( ',', $key );
			$grid[ $y ][ $x ] = '#'; // Mark robot positions
		}

		// Write the grid to the file.
		$content = '';
		foreach ( $grid as $line ) {
			$content .= $line . PHP_EOL;
		}

		file_put_contents( $filePath, $content );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-14-test.txt' : '/data/day-14.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$robots = [];
		foreach ( $lines as $line ) {
			if ( preg_match( '/p=(-?\d+),(-?\d+) v=(-?\d+),(-?\d+)/', $line, $matches ) ) {
				$robots[] = [
					'p' => [ (int) $matches[1], (int) $matches[2] ],
					'v' => [ (int) $matches[3], (int) $matches[4] ],
				];
			}
		}

		return $robots;
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
	$day14  = new Day14( $test, $part );
	$result = $day14->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 12,
			'real' => 215476074,
		],
		2 => [
			'test' => 1,
			'real' => 6285,
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
