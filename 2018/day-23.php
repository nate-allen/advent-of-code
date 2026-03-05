<?php

namespace AdventOfCode\Year2018;

/**
 * Day 23: Experimental Emergency Teleportation
 */
class Day23 {
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
	 * Part 1: Find the nanobot with the largest signal radius and count nanobots in range.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		// Find the nanobot with the largest radius
		$strongest = null;
		$max_r     = -1;

		foreach ( $this->data as $bot ) {
			if ( $bot['r'] > $max_r ) {
				$max_r     = $bot['r'];
				$strongest = $bot;
			}
		}

		if ( $strongest === null ) {
			return 0;
		}

		// Count nanobots within range of the strongest nanobot
		$count = 0;
		foreach ( $this->data as $bot ) {
			$distance = $this->manhattan_distance( $strongest['pos'], $bot['pos'] );
			if ( $distance <= $strongest['r'] ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Part 2: Find the coordinate in range of the most nanobots, closest to origin.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		if ( empty( $this->data ) ) {
			return 0;
		}

		// Create initial bounding box
		$min_x = $min_y = $min_z = PHP_INT_MAX;
		$max_x = $max_y = $max_z = PHP_INT_MIN;

		foreach ( $this->data as $bot ) {
			$min_x = min( $min_x, $bot['pos'][0] );
			$max_x = max( $max_x, $bot['pos'][0] );
			$min_y = min( $min_y, $bot['pos'][1] );
			$max_y = max( $max_y, $bot['pos'][1] );
			$min_z = min( $min_z, $bot['pos'][2] );
			$max_z = max( $max_z, $bot['pos'][2] );
		}

		$initial_box = [
			'min' => [ $min_x, $min_y, $min_z ],
			'max' => [ $max_x, $max_y, $max_z ],
		];

		$queue = new \SplPriorityQueue();
		// Extract both data and priority
		$queue->setExtractFlags( \SplPriorityQueue::EXTR_BOTH );

		$initial_upper = $this->calculate_upper_bound( $initial_box );
		$initial_dist  = $this->box_distance_to_origin( $initial_box );
		$initial_size  = $this->box_size( $initial_box );

		$multiplier = 1000000000;
		$priority   = $initial_upper * $multiplier - $initial_dist + $initial_size / $multiplier;

		$queue->insert(
			[
				'box'     => $initial_box,
				'upper'   => $initial_upper,
				'dist'    => $initial_dist,
				'size'    => $initial_size,
			],
			$priority
		);

		$best_count    = 0;
		$best_distance = PHP_INT_MAX;

		while ( ! $queue->isEmpty() ) {
			$item        = $queue->extract();
			$data        = $item['data'];
			$box         = $data['box'];
			$upper_bound = $data['upper'];

			// If upper bound can't beat current best, skip
			if ( $upper_bound < $best_count ) {
				continue;
			}

			if ( $this->is_point_box( $box ) ) {
				// This is a single point, calculate exact count
				$point = $box['min'];
				$count = $this->count_bots_in_range( $point );
				$dist  = $this->manhattan_distance( $point, [ 0, 0, 0 ] );

				if ( $count > $best_count || ($count === $best_count && $dist < $best_distance) ) {
					$best_count    = $count;
					$best_distance = $dist;
				}
				break;
			} else {
				// Split box and add promising octants to queue
				$octants = $this->split_box( $box );
				foreach ( $octants as $octant ) {
					$upper = $this->calculate_upper_bound( $octant );
					$dist  = $this->box_distance_to_origin( $octant );
					$size  = $this->box_size( $octant );

					if ( $upper >= $best_count ) {
						$priority = $upper * $multiplier - $dist + $size / $multiplier;
						$queue->insert(
							[
								'box'   => $octant,
								'upper' => $upper,
								'dist'  => $dist,
								'size'  => $size,
							],
							$priority
						);
					}
				}
			}
		}

		return $best_distance;
	}

	/**
	 * Calculates the upper bound (maximum bots that could intersect a box).
	 *
	 * @param array $box Box with 'min' and 'max' keys.
	 *
	 * @return int
	 */
	private function calculate_upper_bound( array $box ): int {
		$count = 0;
		foreach ( $this->data as $bot ) {
			if ( $this->box_intersects_bot( $box, $bot ) ) {
				$count++;
			}
		}
		return $count;
	}

	/**
	 * Finds the closest point in a box to a target point.
	 *
	 * @param array $box Box with 'min' and 'max' keys.
	 * @param array $target Target point [x, y, z].
	 *
	 * @return array Closest point [x, y, z].
	 */
	private function box_closest_point( array $box, array $target ): array {
		$result = [];
		for ( $i = 0; $i < 3; $i++ ) {
			$result[$i] = max( $box['min'][$i], min( $target[$i], $box['max'][$i] ) );
		}
		return $result;
	}

	/**
	 * Checks if a box could intersect a bot's range.
	 *
	 * @param array $box Box with 'min' and 'max' keys.
	 * @param array $bot Bot with 'pos' and 'r' keys.
	 *
	 * @return bool
	 */
	private function box_intersects_bot( array $box, array $bot ): bool {
		$closest  = $this->box_closest_point( $box, $bot['pos'] );
		$distance = $this->manhattan_distance( $closest, $bot['pos'] );
		return $distance <= $bot['r'];
	}

	/**
	 * Finds the point in a box closest to the origin (0,0,0).
	 *
	 * @param array $box Box with 'min' and 'max' keys.
	 *
	 * @return array Closest point [x, y, z].
	 */
	private function box_closest_point_to_origin( array $box ): array {
		return $this->box_closest_point( $box, [ 0, 0, 0 ] );
	}

	/**
	 * Calculates the Manhattan distance from origin to closest point in box.
	 *
	 * @param array $box Box with 'min' and 'max' keys.
	 *
	 * @return int
	 */
	private function box_distance_to_origin( array $box ): int {
		$closest = $this->box_closest_point_to_origin( $box );
		return $this->manhattan_distance( $closest, [ 0, 0, 0 ] );
	}

	/**
	 * Splits a box into 8 octants.
	 *
	 * @param array $box Box with 'min' and 'max' keys.
	 *
	 * @return array Array of 8 boxes.
	 */
	private function split_box( array $box ): array {
		$mid = [];
		for ( $i = 0; $i < 3; $i++ ) {
			$mid[$i] = (int) floor( ($box['min'][$i] + $box['max'][$i]) / 2 );
		}

		$octants = [];
		for ( $bits = 0; $bits < 8; $bits++ ) {
			$new_min = [];
			$new_max = [];
			for ( $i = 0; $i < 3; $i++ ) {
				if ( ($bits >> $i) & 1 ) {
					$new_min[$i] = $mid[$i] + 1;
					$new_max[$i] = $box['max'][$i];
				} else {
					$new_min[$i] = $box['min'][$i];
					$new_max[$i] = $mid[$i];
				}
			}
			// Only add non-empty boxes
			$valid = true;
			for ( $i = 0; $i < 3; $i++ ) {
				if ( $new_min[$i] > $new_max[$i] ) {
					$valid = false;
					break;
				}
			}
			if ( $valid ) {
				$octants[] = [
					'min' => $new_min,
					'max' => $new_max,
				];
			}
		}
		return $octants;
	}

	/**
	 * Checks if a box represents a single point.
	 *
	 * @param array $box Box with 'min' and 'max' keys.
	 *
	 * @return bool
	 */
	private function is_point_box( array $box ): bool {
		return $box['min'][0] === $box['max'][0] &&
			$box['min'][1] === $box['max'][1] &&
			$box['min'][2] === $box['max'][2];
	}

	/**
	 * Calculates the size (volume) of a box.
	 *
	 * @param array $box Box with 'min' and 'max' keys.
	 *
	 * @return int
	 */
	private function box_size( array $box ): int {
		$size = 1;
		for ( $i = 0; $i < 3; $i++ ) {
			$dim_size = $box['max'][$i] - $box['min'][$i] + 1;
			if ( $dim_size <= 0 ) {
				return 0;
			}

			// If size * dim_size would exceed PHP_INT_MAX, cap it
			if ( $size > 0 && $dim_size > PHP_INT_MAX / $size ) {
				return PHP_INT_MAX;
			}
			$size *= $dim_size;
		}
		return $size;
	}

	/**
	 * Counts how many bots have a point within their range.
	 *
	 * @param array $point Point [x, y, z].
	 *
	 * @return int
	 */
	private function count_bots_in_range( array $point ): int {
		$count = 0;
		foreach ( $this->data as $bot ) {
			$distance = $this->manhattan_distance( $point, $bot['pos'] );
			if ( $distance <= $bot['r'] ) {
				$count++;
			}
		}
		return $count;
	}

	/**
	 * Calculates the Manhattan distance between two 3D points.
	 *
	 * @param array $pos1 First position [x, y, z].
	 * @param array $pos2 Second position [x, y, z].
	 *
	 * @return int
	 */
	private function manhattan_distance( array $pos1, array $pos2 ): int {
		return abs( $pos1[0] - $pos2[0] ) + abs( $pos1[1] - $pos2[1] ) + abs( $pos1[2] - $pos2[2] );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		if ( $test ) {
			$file = $this->part === 1 ? '/data/day-23-test1.txt' : '/data/day-23-test2.txt';
		} else {
			$file = '/data/day-23.txt';
		}
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$nanobots = [];
		foreach ( $lines as $line ) {
			if ( preg_match( '/pos=<(-?\d+),(-?\d+),(-?\d+)>, r=(\d+)/', $line, $matches ) ) {
				$nanobots[] = [
					'pos' => [ (int) $matches[1], (int) $matches[2], (int) $matches[3] ],
					'r'   => (int) $matches[4],
				];
			}
		}

		return $nanobots;
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
	$day23  = new Day23( $test, $part );
	$result = $day23->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 7,
			'real' => 497,
		],
		2 => [
			'test' => 36,
			'real' => 85761543,
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
