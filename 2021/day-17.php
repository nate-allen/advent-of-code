<?php

namespace AdventOfCode\Year2021;

/**
 * Day 17: Trick Shot
 */
class Day17 {
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
	 * Part 1: Find the highest y position the probe reaches while still landing in the target area.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$target     = $this->data;
		$max_height = 0;

		// Loop over possible initial velocities
		for ( $v_x0 = 1; $v_x0 <= $target['x']['max']; $v_x0 ++ ) {
			for ( $v_y0 = $target['y']['min']; $v_y0 < abs( $target['y']['min'] ); $v_y0 ++ ) {
				if ( $this->lands_in_target( $v_x0, $v_y0, $target['x']['min'], $target['x']['max'], $target['y']['min'], $target['y']['max'] ) ) {
					// Calculate the maximum height for this trajectory
					$height     = ( $v_y0 * ( $v_y0 + 1 ) ) / 2;
					$max_height = max( $max_height, $height );
				}
			}
		}

		return (int) $max_height;
	}

	/**
	 * Part 2: Count the number of distinct initial velocity values that cause the probe to land in the target area.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$target = $this->data;
		$count  = 0;

		// Loop over possible initial velocities
		for ( $v_x0 = 1; $v_x0 <= $target['x']['max']; $v_x0 ++ ) {
			for ( $v_y0 = $target['y']['min']; $v_y0 < abs( $target['y']['min'] ); $v_y0 ++ ) {
				if ( $this->lands_in_target( $v_x0, $v_y0, $target['x']['min'], $target['x']['max'], $target['y']['min'], $target['y']['max'] ) ) {
					$count ++;
				}
			}
		}

		return $count;
	}

	/**
	 * Simulates the probe's trajectory to determine if it lands in the target area.
	 *
	 * @param integer $v_x0 Initial x velocity.
	 * @param integer $v_y0 Initial y velocity.
	 * @param integer $x_min Target area x minimum.
	 * @param integer $x_max Target area x maximum.
	 * @param integer $y_min Target area y minimum.
	 * @param integer $y_max Target area y maximum.
	 *
	 * @return bool True if the probe lands in the target area, false otherwise.
	 */
	private function lands_in_target( int $v_x0, int $v_y0, int $x_min, int $x_max, int $y_min, int $y_max ): bool {
		$x  = 0;
		$y  = 0;
		$vx = $v_x0;
		$vy = $v_y0;

		while ( $x <= $x_max && $y >= $y_min ) {
			// Update position
			$x += $vx;
			$y += $vy;

			// Check if within target area
			if ( $x >= $x_min && $x <= $x_max && $y >= $y_min && $y <= $y_max ) {
				return true;
			}

			// Update velocities
			$vx = max( 0, $vx - 1 ); // Drag reduces x velocity
			$vy --; // Gravity affects y velocity
		}

		return false;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-17-test.txt' : '/data/day-17.txt';

		preg_match('/x=(\d+)\.\.(\d+), y=(-?\d+)\.\.(-?\d+)/', trim( file_get_contents( __DIR__ . $file ) ), $matches);

		return [
			'x' => [
				'min' => (int)$matches[1],
				'max' => (int)$matches[2]
			],
			'y' => [
				'min' => (int)$matches[3],
				'max' => (int)$matches[4]
			]
		];
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
	$day17  = new Day17( $test, $part );
	$result = $day17->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 45,
			'real' => 5886,
		],
		2 => [
			'test' => 112,
			'real' => 1806,
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
