<?php

namespace AdventOfCode\Year2016;

/**
 * Day 20: Firewall Rules
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
	 * Sorted, merged blocked IP ranges.
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
	 * Part 1: Find lowest unblocked IP.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$ranges = $this->data;

		if ( $ranges[0][0] > 0 ) {
			return 0;
		}

		return $ranges[0][1] + 1;
	}

	/**
	 * Part 2: Count total allowed IPs.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$max     = $this->is_test ? 9 : 4294967295;
		$blocked = 0;

		foreach ( $this->data as [ $lo, $hi ] ) {
			$blocked += $hi - $lo + 1;
		}

		return $max - $blocked + 1;
	}

	/**
	 * Parses the puzzle input data into sorted, merged ranges.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Merged blocked ranges sorted by start.
	 */
	private function parse_data( bool $test ): array {
		$file   = $test ? '/data/day-20-test.txt' : '/data/day-20.txt';
		$lines  = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$ranges = [];

		foreach ( $lines as $line ) {
			[ $lo, $hi ] = explode( '-', $line );
			$ranges[] = [ (int) $lo, (int) $hi ];
		}

		usort( $ranges, fn( $a, $b ) => $a[0] <=> $b[0] );

		// Merge overlapping ranges.
		$merged = [ $ranges[0] ];

		for ( $i = 1; $i < count( $ranges ); $i++ ) {
			$last = count( $merged ) - 1;

			if ( $ranges[ $i ][0] <= $merged[ $last ][1] + 1 ) {
				$merged[ $last ][1] = max( $merged[ $last ][1], $ranges[ $i ][1] );
			} else {
				$merged[] = $ranges[ $i ];
			}
		}

		return $merged;
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
			'test' => 3,
			'real' => 14975795,
		],
		2 => [
			'test' => 2,
			'real' => 101,
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
