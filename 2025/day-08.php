<?php

namespace AdventOfCode\Year2025;

/**
 * Day 08: Playground
 */
class Day08 {
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
	 * Part 1: Connect 1000 pairs of closest junction boxes and return the product of the three largest circuit sizes.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$boxes = $this->data;
		$n     = count( $boxes );
		$limit = $this->is_test ? 10 : 1000;
		$heap  = new \SplMinHeap();

		for ( $i = 0; $i < $n; $i++ ) {
			for ( $j = $i + 1; $j < $n; $j++ ) {
				$dx       = $boxes[ $i ][0] - $boxes[ $j ][0];
				$dy       = $boxes[ $i ][1] - $boxes[ $j ][1];
				$dz       = $boxes[ $i ][2] - $boxes[ $j ][2];
				$distance = $dx * $dx + $dy * $dy + $dz * $dz;

				$heap->insert( [$distance, $i, $j] );
			}
		}

		$parent = range( 0, $n - 1 );
		$size   = array_fill( 0, $n, 1 );

		$processed = 0;
		while ( $processed < $limit && ! $heap->isEmpty() ) {
			$pair = $heap->extract();
			$this->merge_circuits( $pair[1], $pair[2], $parent, $size );
			$processed++;
		}
		$circuit_sizes = [];
		for ( $i = 0; $i < $n; $i++ ) {
			$root                   = $this->find( $i, $parent );
			$circuit_sizes[ $root ] = ($circuit_sizes[ $root ] ?? 0) + 1;
		}

		rsort( $circuit_sizes );

		return $circuit_sizes[0] * $circuit_sizes[1] * $circuit_sizes[2];
	}

	/**
	 * Part 2: Connect all junction boxes until they form a single circuit, then return the product of X coordinates of the last connecting pair.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$boxes = $this->data;
		$n     = count( $boxes );
		$heap  = new \SplMinHeap();

		for ( $i = 0; $i < $n; $i++ ) {
			for ( $j = $i + 1; $j < $n; $j++ ) {
				$dx           = $boxes[ $i ][0] - $boxes[ $j ][0];
				$dy           = $boxes[ $i ][1] - $boxes[ $j ][1];
				$dz           = $boxes[ $i ][2] - $boxes[ $j ][2];
				$dist_squared = $dx * $dx + $dy * $dy + $dz * $dz;

				$heap->insert( [$dist_squared, $i, $j] );
			}
		}

		$parent = range( 0, $n - 1 );
		$size   = array_fill( 0, $n, 1 );

		while ( ! $heap->isEmpty() ) {
			$pair = $heap->extract();
			$i    = $pair[1];
			$j    = $pair[2];

			$merged = $this->merge_circuits( $i, $j, $parent, $size );

			if ( $merged ) {
				$final_root = $this->find( $i, $parent );
				if ( $size[ $final_root ] === $n ) {
					return $boxes[ $i ][0] * $boxes[ $j ][0];
				}
			}
		}

		// Should never reach here...
		return 0;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of junction box coordinates [x, y, z]
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-08-test.txt' : '/data/day-08.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$boxes = [];
		foreach ( $lines as $line ) {
			$coords  = explode( ',', $line );
			$boxes[] = [
				(int) $coords[0],
				(int) $coords[1],
				(int) $coords[2],
			];
		}

		return $boxes;
	}

	/**
	 * Finds the root of a node with path compression.
	 *
	 * @param int   $x      The node to find the root for.
	 * @param array $parent Reference to the parent array.
	 *
	 * @return int The root node.
	 */
	private function find( int $x, array &$parent ): int {
		if ( $parent[ $x ] !== $x ) {
			$parent[ $x ] = $this->find( $parent[ $x ], $parent );
		}
		return $parent[ $x ];
	}

	/**
	 * Merges two circuits
	 *
	 * @param int   $a      First node.
	 * @param int   $b      Second node.
	 * @param array $parent Reference to the parent array.
	 * @param array $size   Reference to the size array.
	 *
	 * @return bool True if the sets were merged, false if they were already in the same set.
	 */
	private function merge_circuits( int $a, int $b, array &$parent, array &$size ): bool {
		$root_a = $this->find( $a, $parent );
		$root_b = $this->find( $b, $parent );

		if ( $root_a === $root_b ) {
			return false;
		}

		if ( $size[ $root_a ] < $size[ $root_b ] ) {
			$temp   = $root_a;
			$root_a = $root_b;
			$root_b = $temp;
		}

		$parent[ $root_b ] = $root_a;
		$size[ $root_a ]  += $size[ $root_b ];

		return true;
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
	$day08  = new Day08( $test, $part );
	$result = $day08->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 40,
			'real' => 42840,
		],
		2 => [
			'test' => 25272,
			'real' => 170629052,
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
