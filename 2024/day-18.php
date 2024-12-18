<?php

namespace AdventOfCode\Year2024;

ini_set('memory_limit', '1024M');

/**
 * Day 18: RAM Run
 */
class Day18 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var int
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

	private array $directions = [
		[ 0, 1 ],
		[ 1, 0 ],
		[ 0, - 1 ],
		[ - 1, 0 ],
	];

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return int
	 */
	public function run(): int|string {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Simulate falling bytes onto a grid, and find the shortest path to the exit.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$grid_size = $this->is_test ? 6 : 70;
		$limit     = $this->is_test ? 12 : 1024;
		$grid      = array_fill( 0, $grid_size + 1, array_fill( 0, $grid_size + 1, '.' ) );

		// Simulate falling bytes.
		foreach ( $this->data as $index => $line ) {
			[ $x, $y ] = array_map( 'intval', explode( ',', $line ) );
			$grid[ $y ][ $x ] = '#';

			// Stop after a certain number of bytes.
			if ( $index >= $limit ) {
				break;
			}
		}

		// Find the shortest path using BFS
		return $this->find_shortest_path( $grid, $grid_size );
	}

	/**
	 * Find the shortest path from the start to the exit.
	 *
	 * @param array   $grid      The grid with bytes and empty spaces.
	 * @param integer $grid_size The size of the grid.
	 *
	 * @return integer
	 */
	private function find_shortest_path( array $grid, int $grid_size ): int {
		$queue      = [ [ 0, 0, 0 ] ]; // [x, y, steps]
		$visited    = [];

		while ( ! empty( $queue ) ) {
			[ $x, $y, $steps ] = array_shift( $queue );

			if ( $x === $grid_size && $y === $grid_size ) {
				return $steps; // Reached the target
			}

			foreach ( $this->directions as [$dx, $dy] ) {
				$nx = $x + $dx;
				$ny = $y + $dy;

				if ( $nx >= 0 && $nx <= $grid_size && $ny >= 0 && $ny <= $grid_size &&
					$grid[ $ny ][ $nx ] === '.' && ! isset( $visited["$nx,$ny"] ) ) {
					$queue[]            = [ $nx, $ny, $steps + 1 ];
					$visited["$nx,$ny"] = true;
				}
			}
		}

		return - 1; // No path found
	}

	/**
	 * Part 2: Find the coordinates of the first byte that will prevent the exit from being reachable.
	 *
	 * @retur string
	 */
	private function solve_part_2(): string {
		$grid_size = $this->is_test ? 6 : 70;
		$grid      = array_fill( 0, $grid_size + 1, array_fill( 0, $grid_size + 1, '.' ) );

		$low  = $this->is_test ? 12 : 1024; // Minimum number of bytes.
		$high = count( $this->data ); // Total number of bytes.

		while ( $low < $high ) {
			$mid = intdiv( $low + $high, 2 );

			// Reset the grid for simulation up to the `mid` byte.
			foreach ( $grid as &$row ) {
				$row = array_fill( 0, $grid_size + 1, '.' );
			}

			// Simulate bytes falling up to the `mid` index.
			for ( $i = 0; $i <= $mid; $i ++ ) {
				[ $x, $y ] = $this->data[ $i ];
				$grid[ $y ][ $x ] = '#';
			}

			// Check if the path is blocked.
			if ( $this->find_shortest_path( $grid, $grid_size ) === - 1 ) {
				// Path is blocked. Narrow the range to the lower half.
				$high = $mid;
			} else {
				// Path is still open. Narrow the range to the upper half.
				$low = $mid + 1;
			}
		}

		return "{$this->data[ $low ][0]},{$this->data[ $low ][1]}";
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data(bool $test): array {
		$file   = $test ? '/data/day-18-test.txt' : '/data/day-18.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( fn( $line ) => array_map( 'intval', explode( ',', $line ) ), $lines );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param int $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day18  = new Day18( $test, $part );
	$result = $day18->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 22,
			'real' => 260,
		],
		2 => [
			'test' => '6,1',
			'real' => '24,48',
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
