<?php

namespace AdventOfCode\Year2016;

/**
 * Day 24: Air Duct Spelunking
 */
class Day24 {
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
	 * Parsed data: ['grid' => array, 'points' => array of digit => [x, y]].
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
	 * Part 1: Shortest route visiting all points starting from 0.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->find_shortest( false );
	}

	/**
	 * Part 2: Shortest route visiting all points and returning to 0.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->find_shortest( true );
	}

	/**
	 * Finds the shortest route visiting all numbered points.
	 *
	 * @param bool $return_to_start Whether to return to 0 at the end.
	 *
	 * @return integer
	 */
	private function find_shortest( bool $return_to_start ): int {
		$grid   = $this->data['grid'];
		$points = $this->data['points'];
		$ids    = array_keys( $points );
		$n      = count( $ids );

		// BFS from each point to every other point.
		$dist = [];
		foreach ( $ids as $id ) {
			$dist[ $id ] = $this->bfs_distances( $grid, $points[ $id ], $points );
		}

		// Try all permutations of non-zero points.
		$others = array_filter( $ids, fn( $id ) => $id !== 0 );
		$others = array_values( $others );
		$min    = PHP_INT_MAX;

		foreach ( $this->permutations( $others ) as $perm ) {
			$total = $dist[0][ $perm[0] ];

			for ( $i = 0; $i < count( $perm ) - 1; $i++ ) {
				$total += $dist[ $perm[ $i ] ][ $perm[ $i + 1 ] ];
			}

			if ( $return_to_start ) {
				$total += $dist[ $perm[ count( $perm ) - 1 ] ][0];
			}

			$min = min( $min, $total );
		}

		return $min;
	}

	/**
	 * BFS from a start position to find distances to all numbered points.
	 *
	 * @param array $grid   The grid.
	 * @param array $start  [row, col] start position.
	 * @param array $points All numbered points.
	 *
	 * @return array Distances keyed by point ID.
	 */
	private function bfs_distances( array $grid, array $start, array $points ): array {
		$queue   = [ [ $start[0], $start[1], 0 ] ];
		$visited = [ $start[0] . ',' . $start[1] => true ];
		$head    = 0;
		$dirs    = [ [ 0, 1 ], [ 0, -1 ], [ 1, 0 ], [ -1, 0 ] ];
		$dists   = [];
		$found   = 0;
		$total   = count( $points );

		while ( $head < count( $queue ) && $found < $total ) {
			[ $r, $c, $steps ] = $queue[ $head++ ];

			foreach ( $points as $id => [ $pr, $pc ] ) {
				if ( $r === $pr && $c === $pc && ! isset( $dists[ $id ] ) ) {
					$dists[ $id ] = $steps;
					$found++;
				}
			}

			foreach ( $dirs as [ $dr, $dc ] ) {
				$nr  = $r + $dr;
				$nc  = $c + $dc;
				$key = "$nr,$nc";

				if ( $grid[ $nr ][ $nc ] === '#' || isset( $visited[ $key ] ) ) {
					continue;
				}

				$visited[ $key ] = true;
				$queue[]         = [ $nr, $nc, $steps + 1 ];
			}
		}

		return $dists;
	}

	/**
	 * Generates all permutations of an array.
	 *
	 * @param array $items Items to permute.
	 *
	 * @return \Generator
	 */
	private function permutations( array $items ): \Generator {
		if ( count( $items ) <= 1 ) {
			yield $items;
			return;
		}

		foreach ( $items as $i => $item ) {
			$rest = array_merge( array_slice( $items, 0, $i ), array_slice( $items, $i + 1 ) );

			foreach ( $this->permutations( $rest ) as $perm ) {
				yield array_merge( [ $item ], $perm );
			}
		}
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Grid and numbered point positions.
	 */
	private function parse_data( bool $test ): array {
		$file   = $test ? '/data/day-24-test.txt' : '/data/day-24.txt';
		$lines  = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$points = [];

		foreach ( $lines as $r => $line ) {
			for ( $c = 0; $c < strlen( $line ); $c++ ) {
				if ( ctype_digit( $line[ $c ] ) ) {
					$points[ (int) $line[ $c ] ] = [ $r, $c ];
				}
			}
		}

		return [ 'grid' => $lines, 'points' => $points ];
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
	$day24  = new Day24( $test, $part );
	$result = $day24->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 14,
			'real' => 448,
		],
		2 => [
			'test' => 0,
			'real' => 672,
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
