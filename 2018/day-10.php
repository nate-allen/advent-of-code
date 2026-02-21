<?php

namespace AdventOfCode\Year2018;

/**
 * Day 10: The Stars Align
 */
class Day10 {
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

	public function __construct( bool $test ) {
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
	}

	/**
	 * Executes the puzzle: finds when stars align and displays the message.
	 *
	 * @return integer The time (in seconds) when the message appears
	 */
	public function run(): int {
		$optimal_time = $this->find_optimal_time();
		$this->render_message( $optimal_time );
		return $optimal_time;
	}

	/**
	 * Finds the time when the bounding box is smallest (stars are most converged).
	 *
	 * @return int The optimal time value
	 */
	private function find_optimal_time(): int {
		$min_box_size = PHP_INT_MAX;
		$optimal_time = 0;
		$time         = 0;

		while ( true ) {
			$box_size = $this->calculate_bounding_box_size( $time );

			if ( $box_size < $min_box_size ) {
				$min_box_size = $box_size;
				$optimal_time = $time;
			} elseif ( $box_size > $min_box_size ) {
				// Once the box size starts increasing, we've passed the optimal time
				break;
			}

			$time++;
		}

		return $optimal_time;
	}

	/**
	 * Calculates the bounding box size at a given time.
	 *
	 * @param int $time The time value
	 *
	 * @return int The sum of width and height of the bounding box
	 */
	private function calculate_bounding_box_size( int $time ): int {
		$min_x = PHP_INT_MAX;
		$max_x = PHP_INT_MIN;
		$min_y = PHP_INT_MAX;
		$max_y = PHP_INT_MIN;

		foreach ( $this->data as $point ) {
			$x = $point['x'] + $point['vx'] * $time;
			$y = $point['y'] + $point['vy'] * $time;

			$min_x = min( $min_x, $x );
			$max_x = max( $max_x, $x );
			$min_y = min( $min_y, $y );
			$max_y = max( $max_y, $y );
		}

		return ($max_x - $min_x) + ($max_y - $min_y);
	}

	/**
	 * Renders the message at a given time by displaying the star positions.
	 *
	 * @param int $time The time value to render
	 *
	 * @return void
	 */
	private function render_message( int $time ): void {
		// Calculate all positions at the given time
		$positions = [];
		$min_x     = PHP_INT_MAX;
		$max_x     = PHP_INT_MIN;
		$min_y     = PHP_INT_MAX;
		$max_y     = PHP_INT_MIN;

		foreach ( $this->data as $point ) {
			$x = $point['x'] + $point['vx'] * $time;
			$y = $point['y'] + $point['vy'] * $time;

			$positions[ $y ][ $x ] = true;

			$min_x = min( $min_x, $x );
			$max_x = max( $max_x, $x );
			$min_y = min( $min_y, $y );
			$max_y = max( $max_y, $y );
		}

		// Render the grid
		$yellow = "\033[33m"; // Yellow text
		$reset  = "\033[0m";  // Reset text formatting
		echo $yellow . 'Message:' . $reset . PHP_EOL;
		for ( $y = $min_y; $y <= $max_y; $y++ ) {
			for ( $x = $min_x; $x <= $max_x; $x++ ) {
				echo isset( $positions[ $y ][ $x ] ) ? '#' : '.';
			}
			echo PHP_EOL;
		}
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of points, each with [x, y, vx, vy]
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-10-test.txt' : '/data/day-10.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$points = [];
		foreach ( $lines as $line ) {
			// Extract all integers from the line: position=<x, y> velocity=<vx, vy>
			if ( preg_match_all( '/-?\d+/', $line, $matches ) ) {
				$numbers = array_map( 'intval', $matches[0] );
				if ( count( $numbers ) === 4 ) {
					$points[] = [
						'x'  => $numbers[0],
						'y'  => $numbers[1],
						'vx' => $numbers[2],
						'vy' => $numbers[3],
					];
				}
			}
		}

		return $points;
	}
}

/**
 * Runs the puzzle with the given settings and outputs results.
 *
 * @param bool $test Whether to use test data.
 */
function run_puzzle( bool $test ): void {
	$start  = microtime( true );
	$day10  = new Day10( $test );
	$result = $day10->run();
	$end    = microtime( true );

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( $yellow . 'Seconds:  ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for test mode
while ( true ) {
	$test = strtolower( trim( readline( 'Do you want to run the test? (y/n): ' ) ) );
	if ( in_array( $test, [ 'y', 'n' ], true ) ) {
		$test_mode = $test === 'y';
		run_puzzle( $test_mode );
		break;
	}
	echo 'Invalid input. Please enter y or n.' . PHP_EOL;
}
