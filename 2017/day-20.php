<?php

namespace AdventOfCode\Year2017;

/**
 * Day 20: Particle Swarm
 */
class Day20 {
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
	 * Part 1: Find the particle that stays closest to the origin in the long term.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$min_accel = PHP_INT_MAX;
		$closest   = 0;

		foreach ( $this->data as $i => $particle ) {
			$accel = abs( $particle['a'][0] ) + abs( $particle['a'][1] ) + abs( $particle['a'][2] );
			$vel   = abs( $particle['v'][0] ) + abs( $particle['v'][1] ) + abs( $particle['v'][2] );
			$pos   = abs( $particle['p'][0] ) + abs( $particle['p'][1] ) + abs( $particle['p'][2] );

			if ( $accel < $min_accel
				|| ( $accel === $min_accel && $vel < $min_vel )
				|| ( $accel === $min_accel && $vel === $min_vel && $pos < $min_pos ) ) {
				$min_accel = $accel;
				$min_vel   = $vel;
				$min_pos   = $pos;
				$closest   = $i;
			}
		}

		return $closest;
	}

	/**
	 * Part 2: Simulate particles and remove collisions, count survivors.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$particles = $this->data;

		for ( $tick = 0; $tick < 1000; $tick++ ) {
			// Update velocities and positions.
			foreach ( $particles as $i => &$p ) {
				for ( $d = 0; $d < 3; $d++ ) {
					$p['v'][ $d ] += $p['a'][ $d ];
					$p['p'][ $d ] += $p['v'][ $d ];
				}
			}
			unset( $p );

			// Find collisions.
			$positions = [];
			foreach ( $particles as $i => $p ) {
				$key = implode( ',', $p['p'] );
				$positions[ $key ][] = $i;
			}

			// Remove colliding particles.
			foreach ( $positions as $group ) {
				if ( count( $group ) > 1 ) {
					foreach ( $group as $i ) {
						unset( $particles[ $i ] );
					}
				}
			}
		}

		return count( $particles );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? "/data/day-20-test{$this->part}.txt" : '/data/day-20.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( function ( $line ) {
			preg_match_all( '/-?\d+/', $line, $matches );
			$nums = array_map( 'intval', $matches[0] );

			return [
				'p' => [ $nums[0], $nums[1], $nums[2] ],
				'v' => [ $nums[3], $nums[4], $nums[5] ],
				'a' => [ $nums[6], $nums[7], $nums[8] ],
			];
		}, $lines );
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
	$day20  = new Day20( $test, $part );
	$result = $day20->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 0,
			'real' => 376,
		],
		2 => [
			'test' => 1,
			'real' => 574,
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
