<?php

namespace AdventOfCode\Year2021;

/**
 * Day 22: Reactor Reboot
 */
class Day22 {
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
	 * Part 1: Execute reboot steps and count how many cubes in the region x=-50..50, y=-50..50, z=-50..50 are turned on.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		// Prepare a 3D "off" grid for x,y,z in [-50..50].
		// Store booleans in a nested array or use an offset-based approach.
		$grid = [];

		// Initialize the entire 101x101x101 region to false (off).
		for ( $x = - 50; $x <= 50; $x ++ ) {
			$grid[ $x ] = [];
			for ( $y = - 50; $y <= 50; $y ++ ) {
				$grid[ $x ][ $y ] = [];
				for ( $z = - 50; $z <= 50; $z ++ ) {
					$grid[ $x ][ $y ][ $z ] = false;
				}
			}
		}

		// Apply each instruction in order.
		foreach ( $this->data as $step ) {
			// Determine whether it is an 'on' or 'off' step.
			$is_on  = isset( $step['on'] );
			$ranges = $is_on ? $step['on'] : $step['off'];
			[ $x_range, $y_range, $z_range ] = $ranges;
			[ $x1, $x2 ] = $x_range;
			[ $y1, $y2 ] = $y_range;
			[ $z1, $z2 ] = $z_range;

			// Limit the ranges to [-50..50].
			$x1 = max( $x1, - 50 );
			$x2 = min( $x2, 50 );
			$y1 = max( $y1, - 50 );
			$y2 = min( $y2, 50 );
			$z1 = max( $z1, - 50 );
			$z2 = min( $z2, 50 );

			// If valid range after limiting to 50, set cubes on or off.
			if ( $x1 <= $x2 && $y1 <= $y2 && $z1 <= $z2 ) {
				for ( $x = $x1; $x <= $x2; $x ++ ) {
					for ( $y = $y1; $y <= $y2; $y ++ ) {
						for ( $z = $z1; $z <= $z2; $z ++ ) {
							$grid[ $x ][ $y ][ $z ] = $is_on;
						}
					}
				}
			}
		}

		// Count how many cubes are on.
		$count_on = 0;
		for ( $x = - 50; $x <= 50; $x ++ ) {
			for ( $y = - 50; $y <= 50; $y ++ ) {
				for ( $z = - 50; $z <= 50; $z ++ ) {
					if ( $grid[ $x ][ $y ][ $z ] ) {
						$count_on ++;
					}
				}
			}
		}

		return $count_on;
	}

	/**
	 * Part 2: Execute reboot steps for all cubes and count how many are turned on.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$on_cuboids = [];

		foreach ( $this->data as $step ) {
			$is_on  = isset( $step['on'] );
			$ranges = $is_on ? $step['on'] : $step['off'];
			[ [ $x1, $x2 ], [ $y1, $y2 ], [ $z1, $z2 ] ] = $ranges;
			$new_cuboid = [ $x1, $x2, $y1, $y2, $z1, $z2 ];

			// First, remove (subtract) the new_cuboid from all existing 'on' cuboids
			// to eliminate overlap. We'll build a new list of cuboids:
			$updated = [];
			foreach ( $on_cuboids as $c ) {
				$remaining = $this->subtract_cuboid( $c, $new_cuboid );
				// $remaining could be multiple smaller cubes
				foreach ( $remaining as $r ) {
					$updated[] = $r;
				}
			}
			$on_cuboids = $updated;

			// If we're turning them on, then union = old union minus overlap + new_cuboid
			if ( $is_on ) {
				$on_cuboids[] = $new_cuboid;
			}
		}

		// Finally, compute the sum of volumes of all on-cuboids
		$total_volume = 0;
		foreach ( $on_cuboids as [$x1, $x2, $y1, $y2, $z1, $z2] ) {
			$total_volume += ( $x2 - $x1 + 1 )
							 * ( $y2 - $y1 + 1 )
							 * ( $z2 - $z1 + 1 );
		}

		return $total_volume;
	}

	/**
	 * Subtract $toRemove from $source. Return an array of cuboids
	 * representing $source without the volume that overlaps $toRemove.
	 * Each cuboid is in form [x1, x2, y1, y2, z1, z2].
	 */
	function subtract_cuboid( array $source, array $to_remove ): array {
		// 1. Find the intersection (if none, just return [$source])
		$intersection = $this->intersect_cuboid( $source, $to_remove );
		if ( ! $intersection ) {
			// No intersection, so no removal. Return original as is.
			return [ $source ];
		}

		// intersection = [ix1, ix2, iy1, iy2, iz1, iz2]
		// source       = [sx1, sx2, sy1, sy2, sz1, sz2]

		// 2. Slice the source cuboid into up to 6 pieces surrounding the intersection:
		//    - left slice (x-range: sx1..ix1-1) if it exists
		//    - right slice (x-range: ix2+1..sx2) if it exists
		//    - front slice (y-range: sy1..iy1-1) if it exists, restricted to intersection's x-range
		//    - back slice (y-range: iy2+1..sy2)  if it exists
		//    - bottom slice (z-range: sz1..iz1-1) if it exists
		//    - top slice (z-range: iz2+1..sz2)    if it exists
		//
		//    For each slice, you keep the full range of the other coordinates, *except*
		//    where we already subtracted the intersection region. So we build each piece
		//    carefully, skipping zero or negative extents.
		//
		// In 3D, you basically cut away the intersection volume and keep the leftover "shells."

		// We'll build an array $remaining_cuboids. Each item is [x1, x2, y1, y2, z1, z2].
		$remaining_cuboids = [];

		[ $sx1, $sx2, $sy1, $sy2, $sz1, $sz2 ] = $source;
		[ $ix1, $ix2, $iy1, $iy2, $iz1, $iz2 ] = $intersection;

		// Left slice
		if ( $ix1 > $sx1 ) {
			$remaining_cuboids[] = [
				$sx1,
				$ix1 - 1,
				$sy1,
				$sy2,
				$sz1,
				$sz2
			];
		}

		// Right slice
		if ( $ix2 < $sx2 ) {
			$remaining_cuboids[] = [
				$ix2 + 1,
				$sx2,
				$sy1,
				$sy2,
				$sz1,
				$sz2
			];
		}

		// Now the intersection covers x from ix1..ix2, so the front/back/bottom/top
		// must be cut from that narrower x-range [ix1..ix2].
		$x_min = max( $sx1, $ix1 );
		$x_max = min( $sx2, $ix2 );

		// Front slice
		if ( $iy1 > $sy1 ) {
			$remaining_cuboids[] = [
				$x_min,
				$x_max,
				$sy1,
				$iy1 - 1,
				$sz1,
				$sz2
			];
		}

		// Back slice
		if ( $iy2 < $sy2 ) {
			$remaining_cuboids[] = [
				$x_min,
				$x_max,
				$iy2 + 1,
				$sy2,
				$sz1,
				$sz2
			];
		}

		// Similarly for the z dimension, restricted to the intersection's x and y.
		$y_min = max( $sy1, $iy1 );
		$y_max = min( $sy2, $iy2 );

		// Bottom slice
		if ( $iz1 > $sz1 ) {
			$remaining_cuboids[] = [
				$x_min,
				$x_max,
				$y_min,
				$y_max,
				$sz1,
				$iz1 - 1
			];
		}

		// Top slice
		if ( $iz2 < $sz2 ) {
			$remaining_cuboids[] = [
				$x_min,
				$x_max,
				$y_min,
				$y_max,
				$iz2 + 1,
				$sz2
			];
		}

		return $remaining_cuboids;
	}

	/**
	 * Intersection of two cuboids, or null if they don't overlap.
	 */
	function intersect_cuboid( array $a, array $b ): ?array {
		[ $ax1, $ax2, $ay1, $ay2, $az1, $az2 ] = $a;
		[ $bx1, $bx2, $by1, $by2, $bz1, $bz2 ] = $b;

		$ix1 = max( $ax1, $bx1 );
		$ix2 = min( $ax2, $bx2 );
		$iy1 = max( $ay1, $by1 );
		$iy2 = min( $ay2, $by2 );
		$iz1 = max( $az1, $bz1 );
		$iz2 = min( $az2, $bz2 );

		if ( $ix1 <= $ix2 && $iy1 <= $iy2 && $iz1 <= $iz2 ) {
			return [ $ix1, $ix2, $iy1, $iy2, $iz1, $iz2 ];
		}

		return null;
	}


	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-22-test.txt' : '/data/day-22.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( function ( $line ) {
			preg_match( '/^(on|off)\s+x=(-?\d+)\.\.(-?\d+),y=(-?\d+)\.\.(-?\d+),z=(-?\d+)\.\.(-?\d+)$/', $line, $matches );

			return [
				$matches[1] => [
					[ (int) $matches[2], (int) $matches[3] ],
					[ (int) $matches[4], (int) $matches[5] ],
					[ (int) $matches[6], (int) $matches[7] ],
				],
			];
		}, $lines );
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
	$day22  = new Day22( $test, $part );
	$result = $day22->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 474140,
			'real' => 611176,
		],
		2 => [
			'test' => 2758514936282235,
			'real' => 0,
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
