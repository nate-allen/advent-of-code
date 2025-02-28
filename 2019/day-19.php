<?php

namespace AdventOfCode\Year2019;

include_once 'lib/IntcodeComputer.php';

/**
 * Day 19: Tractor Beam
 */
class Day19 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var integer
	 */
	private int $part;

	/**
	 * Parsed data from the input file.
	 *
	 * @var array
	 */
	private array $data;

	public function __construct( int $part ) {
		$this->part = $part;
		$this->data = $this->parse_data();
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
	 * Part 1: Scan the 50x50 grid and count how many points are affected by the tractor beam.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$count = 0;

		// Loop through the 50x50 grid.
		for ( $y = 0; $y < 50; $y ++ ) {
			for ( $x = 0; $x < 50; $x ++ ) {
				$count += $this->check_beam( $x, $y );
			}
		}

		return $count;
	}

	/**
	 * Part 2: Find the top-left coordinate of a 100x100 square that fits entirely within the tractor beam.
	 *
	 * Scan rows (increasing y) and for each row advance the x coordinate to the first point where the beam is active.
	 * Then, check whether the beam is active in the top-right corner of the square (which is at x + 99, y - 99).
	 * When it is, the square fits entirely in the beam, and the answer is computed as (x * 10000 + (y - 99)).
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$square_size = 100;
		$x           = 0;

		// Start scanning from y = square_size (the minimum y where a square might fit)
		for ( $y = $square_size; ; $y ++ ) {
			// Move right until the beam is active at (x, y)
			while ( $this->check_beam( $x, $y ) !== 1 ) {
				$x ++;
			}

			// Check if the beam is also active at the top-right corner of the square.
			if ( $this->check_beam( $x + $square_size - 1, $y - $square_size + 1 ) === 1 ) {
				$result_x = $x;
				$result_y = $y - $square_size + 1;

				return $result_x * 10000 + $result_y;
			}
		}
	}

	/**
	 * Checks whether the tractor beam is active at the specified coordinate.
	 *
	 * @param int $x The X coordinate.
	 * @param int $y The Y coordinate.
	 *
	 * @return int Returns 1 if the tractor beam is active, 0 otherwise.
	 */
	private function check_beam( int $x, int $y ): int {
		$computer = new IntcodeComputer( $this->data );

		$computer->add_input( $x );
		$computer->add_input( $y );

		$output = $computer->run_until_output();

		return $output;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @return array
	 */
	private function parse_data(): array {
		return array_map( 'intval', explode( ",", trim( file_get_contents( __DIR__ . '/data/day-19.txt' ) ) ) );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 */
function run_part( int $part ): void {
	$start  = microtime( true );
	$day19  = new Day19( $part );
	$result = $day19->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => 231,
		2 => 9210745,
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $expected_values[ $part ] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for part and test mode
while ( true ) {
	$part = (int) trim( readline( 'Which part do you want to run? (1/2): ' ) );
	if ( ! in_array( $part, [ 1, 2 ], true ) ) {
		echo 'Invalid part. Please enter 1 or 2.' . PHP_EOL;
		continue;
	}

	run_part( $part );
	break;
}
