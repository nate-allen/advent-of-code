<?php

namespace AdventOfCode\Year2017;

/**
 * Day 12: Digital Plumber
 */
class Day12 {
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
	 * Part 1: How many programs are in the group that contains program ID 0?
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$visited = [];
		$queue   = [ 0 ];

		while ( ! empty( $queue ) ) {
			$current = array_shift( $queue );

			if ( isset( $visited[ $current ] ) ) {
				continue;
			}

			$visited[ $current ] = true;

			foreach ( $this->data[ $current ] as $neighbor ) {
				if ( ! isset( $visited[ $neighbor ] ) ) {
					$queue[] = $neighbor;
				}
			}
		}

		return count( $visited );
	}

	/**
	 * Part 2: How many groups are there in total?
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$visited = [];
		$groups  = 0;

		foreach ( array_keys( $this->data ) as $id ) {
			if ( isset( $visited[ $id ] ) ) {
				continue;
			}

			$groups++;
			$queue = [ $id ];

			while ( ! empty( $queue ) ) {
				$current = array_shift( $queue );

				if ( isset( $visited[ $current ] ) ) {
					continue;
				}

				$visited[ $current ] = true;

				foreach ( $this->data[ $current ] as $neighbor ) {
					if ( ! isset( $visited[ $neighbor ] ) ) {
						$queue[] = $neighbor;
					}
				}
			}
		}

		return $groups;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-12-test.txt' : '/data/day-12.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$graph = [];

		foreach ( $lines as $line ) {
			[ $id, $neighbors ] = explode( ' <-> ', $line );
			$graph[ (int) $id ] = array_map( 'intval', explode( ', ', $neighbors ) );
		}

		return $graph;
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
	$day12  = new Day12( $test, $part );
	$result = $day12->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 6,
			'real' => 175,
		],
		2 => [
			'test' => 2,
			'real' => 213,
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
