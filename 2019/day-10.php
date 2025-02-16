<?php

namespace AdventOfCode\Year2019;

/**
 * Day 10: Monitoring Station
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
	 * Asteroid positions.
	 *
	 * @var array
	 */
	private array $asteroids;

	public function __construct( bool $test, int $part ) {
		$this->part      = $part;
		$this->is_test   = $test;
		$this->asteroids = $this->parse_data( $this->is_test );
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
	 * Part 1: Find the asteroid with the best line of sight.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		[ $station, $max_visible ] = $this->find_best_station();

		return $max_visible;
	}

	/**
	 * Part 2: Find the 200th vaporized asteroid and return X*100 + Y.
	 */
	private function solve_part_2(): int {
		[ $station, ] = $this->find_best_station();
		$vaporized = $this->vaporize_asteroids( $station );

		[ $x, $y ] = $vaporized;

		return $x * 100 + $y;
	}

	/**
	 * Finds the best asteroid location for the station.
	 */
	private function find_best_station(): array {
		$best_station = null;
		$max_visible  = 0;

		foreach ( $this->asteroids as $asteroid ) {
			$visible = $this->count_visible_asteroids( $asteroid );
			if ( $visible > $max_visible ) {
				$max_visible  = $visible;
				$best_station = $asteroid;
			}
		}

		return [ $best_station, $max_visible ];
	}

	/**
	 * Counts visible asteroids from a given asteroid.
	 */
	private function count_visible_asteroids( array $asteroid ): int {
		[ $x1, $y1 ] = $asteroid;
		$angles = [];

		foreach ( $this->asteroids as $other ) {
			if ( $other === $asteroid ) {
				continue;
			}

			[ $x2, $y2 ] = $other;

			$angle                = atan2( $y2 - $y1, $x2 - $x1 );
			$angle_key            = sprintf( "%.12f", $angle );
			$angles[ $angle_key ] = true;
		}

		return count( $angles );
	}

	/**
	 * Vaporizes asteroids one-by-one by rotating the laser clockwise.
	 */
	private function vaporize_asteroids( array $station ): array {
		[ $sx, $sy ] = $station;

		// Group asteroids by angle
		$angles_map = [];
		foreach ( $this->asteroids as $asteroid ) {
			if ( $asteroid === $station ) {
				continue;
			}

			[ $x, $y ] = $asteroid;
			$dx = $x - $sx;
			$dy = $y - $sy;

			$angle    = fmod( ( atan2( $dx, - $dy ) + 2 * M_PI ), ( 2 * M_PI ) ); // Adjust angle (0 = up, clockwise)
			$distance = sqrt( $dx ** 2 + $dy ** 2 );

			$angle_key                  = sprintf( "%.12f", $angle );
			$angles_map[ $angle_key ][] = [
				'coords'   => [ $x, $y ],
				'distance' => $distance,
			];
		}

		// Sort each angle group by distance
		foreach ( $angles_map as &$group ) {
			usort( $group, fn( $a, $b ) => $a['distance'] <=> $b['distance'] );
		}

		// Sort angles in ascending order (clockwise from up)
		ksort( $angles_map, SORT_NUMERIC );

		// Vaporize asteroids in rotation
		$vaporized_count = 0;
		while ( true ) {
			foreach ( $angles_map as $angle => &$group ) {
				if ( count( $group ) === 0 ) {
					continue;
				}

				$asteroid = array_shift( $group );
				$vaporized_count ++;

				if ( $vaporized_count === 200 ) {
					return $asteroid['coords'];
				}
			}
		}
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

		$asteroids = [];
		foreach ( $lines as $y => $row ) {
			for ( $x = 0; $x < strlen( $row ); $x ++ ) {
				if ( $row[ $x ] === '#' ) {
					$asteroids[] = [ $x, $y ];
				}
			}
		}

		return $asteroids;
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
	$day10  = new Day10( $test, $part );
	$result = $day10->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 210,
			'real' => 214,
		],
		2 => [
			'test' => 802,
			'real' => 502,
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
