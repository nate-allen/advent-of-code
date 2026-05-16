<?php

namespace AdventOfCode\Year2016;

/**
 * Day 22: Grid Computing
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
	 * Parsed node data: array of [x, y, size, used, avail].
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
	 * Part 1: Count viable pairs of nodes.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$nodes = $this->data;
		$count = 0;
		$n     = count( $nodes );

		for ( $a = 0; $a < $n; $a++ ) {
			if ( $nodes[ $a ]['used'] === 0 ) {
				continue;
			}

			for ( $b = 0; $b < $n; $b++ ) {
				if ( $a === $b ) {
					continue;
				}

				if ( $nodes[ $a ]['used'] <= $nodes[ $b ]['avail'] ) {
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * Part 2: Fewest steps to move goal data to (0,0).
	 *
	 * Nodes are either empty (_), walls (#, too large), or normal (.).
	 * BFS the empty node to just left of G, then shuttle G left with 5-move cycles.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$nodes  = $this->data;
		$max_x  = max( array_column( $nodes, 'x' ) );
		$goal_x = $max_x;
		$goal_y = 0;

		// Find the empty node and build a wall set.
		$empty_x = 0;
		$empty_y = 0;
		$walls   = [];

		// Find the threshold: nodes with used > smallest size of normal nodes are walls.
		$sizes = array_column( $nodes, 'size' );
		sort( $sizes );
		$wall_threshold = $sizes[0]; // Anything with used > min size can't be moved.

		foreach ( $nodes as $node ) {
			if ( $node['used'] === 0 ) {
				$empty_x = $node['x'];
				$empty_y = $node['y'];
			}

			if ( $node['used'] > $wall_threshold ) {
				$walls[ $node['x'] . ',' . $node['y'] ] = true;
			}
		}

		$max_y = max( array_column( $nodes, 'y' ) );

		// BFS to move empty node to (goal_x - 1, 0), avoiding walls and goal position.
		$queue   = [ [ $empty_x, $empty_y, 0 ] ];
		$visited = [ "$empty_x,$empty_y" => true ];
		$head    = 0;
		$dirs    = [ [ 0, 1 ], [ 0, -1 ], [ 1, 0 ], [ -1, 0 ] ];
		$steps   = 0;

		while ( $head < count( $queue ) ) {
			[ $x, $y, $s ] = $queue[ $head++ ];

			if ( $x === $goal_x - 1 && $y === 0 ) {
				$steps = $s;
				break;
			}

			foreach ( $dirs as [ $dx, $dy ] ) {
				$nx  = $x + $dx;
				$ny  = $y + $dy;
				$key = "$nx,$ny";

				if ( $nx < 0 || $ny < 0 || $nx > $max_x || $ny > $max_y ) {
					continue;
				}

				if ( isset( $walls[ $key ] ) || isset( $visited[ $key ] ) ) {
					continue;
				}

				// Can't move through the goal data position.
				if ( $nx === $goal_x && $ny === $goal_y ) {
					continue;
				}

				$visited[ $key ] = true;
				$queue[]         = [ $nx, $ny, $s + 1 ];
			}
		}

		// 1 step to swap empty with G, then 5 steps per position to move G left.
		return $steps + 1 + 5 * ( $goal_x - 1 );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of node data.
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-22-test.txt' : '/data/day-22.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$nodes = [];

		foreach ( $lines as $line ) {
			if ( preg_match( '/node-x(\d+)-y(\d+)\s+(\d+)T\s+(\d+)T\s+(\d+)T/', $line, $m ) ) {
				$nodes[] = [
					'x'     => (int) $m[1],
					'y'     => (int) $m[2],
					'size'  => (int) $m[3],
					'used'  => (int) $m[4],
					'avail' => (int) $m[5],
				];
			}
		}

		return $nodes;
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
	$day22  = new Day22( $test, $part );
	$result = $day22->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 0,
			'real' => 1007,
		],
		2 => [
			'test' => 0,
			'real' => 242,
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
