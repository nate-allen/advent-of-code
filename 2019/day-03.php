<?php

namespace AdventOfCode\Year2019;

/**
 * Day 03: Crossed Wires
 */
class Day03 {
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
	 * Part 1: Trace the wires and find the closest intersection.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		[ $wire_1, $wire_2 ] = $this->data;
		$intersections = [];

		$wire_1_path = $this->trace_wire( $wire_1 );
		$wire_2_path = $this->trace_wire( $wire_2 );

		foreach ( array_keys( $wire_1_path ) as $point ) {
			if ( isset( $wire_2_path[ $point ] ) ) {
				list( $px, $py ) = explode( ',', $point );
				$intersections[] = abs( (int) $px ) + abs( (int) $py );
			}
		}

		return min( $intersections );
	}

	/**
	 * Part 2: Calculate the number of steps each wire takes to reach each intersection; choose the intersection where
	 *         the sum of both wires' steps is lowest. Return the fewest combined steps the wires must take.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		[ $wire_1, $wire_2 ] = $this->data;
		$wire_1_path = $this->trace_wire( $wire_1 );
		$wire_2_path = $this->trace_wire( $wire_2 );

		$min_steps = PHP_INT_MAX;

		// Find intersections and compute the minimum combined steps
		foreach ( array_keys( $wire_1_path ) as $point ) {
			if ( isset( $wire_2_path[ $point ] ) ) {
				$total_steps = $wire_1_path[ $point ] + $wire_2_path[ $point ];
				$min_steps   = min( $min_steps, $total_steps );
			}
		}

		return $min_steps;
	}

	/**
	 * Traces the path of a wire based on the given moves.
	 *
	 * @param array $wire The wire moves.
	 *
	 * @return array
	 */
	private function trace_wire( array $wire ): array {
		$path  = [];
		$x     = $y = 0;
		$steps = 0;

		foreach ( $wire as $move ) {
			$direction = substr( $move, 0, 1 );
			$distance  = (int) substr( $move, 1 );

			for ( $i = 0; $i < $distance; $i ++ ) {
				$steps ++; // Increment step count for every move

				switch ( $direction ) {
					case 'R':
						$x ++;
						break;
					case 'L':
						$x --;
						break;
					case 'U':
						$y ++;
						break;
					case 'D':
						$y --;
						break;
				}

				// Store steps if this position hasn't been visited before
				if ( ! isset( $path["$x,$y"] ) ) {
					$path["$x,$y"] = $steps;
				}
			}
		}

		return $path;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-03-test.txt' : '/data/day-03.txt';
		$wires = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$data = [];

		foreach ( $wires as $wire ) {
			$data[] = explode( ',', $wire );
		}

		return $data;
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
	$day03  = new Day03( $test, $part );
	$result = $day03->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 159,
			'real' => 1225,
		],
		2 => [
			'test' => 610,
			'real' => 107036,
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
