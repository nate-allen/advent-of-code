<?php

namespace AdventOfCode\Year2020;

/**
 * Day 10: Adapter Array
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
	 * Part 1: What is the number of 1-jolt differences multiplied by the number of 3-jolt differences?
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$adapters = $this->data;
		sort( $adapters );

		// Add the charging outlet and the device's built-in adapter
		array_unshift( $adapters, 0 ); // Add the charging outlet (0 jolts)
		$adapters[] = max( $adapters ) + 3; // Add the device's built-in adapter

		// Calculate the differences between consecutive adapters
		$differences = [];
		foreach ( array_keys( $adapters ) as $index ) {
			if ( isset( $adapters[ $index + 1 ] ) ) {
				$differences[] = $adapters[ $index + 1 ] - $adapters[ $index ];
			}
		}

		// Count the occurrences of each difference
		$diffs = array_count_values( $differences );

		// Return the product of the 1-jolt and 3-jolt differences
		return $diffs[1] * $diffs[3];
	}

	/**
	 * Part 2: What is the total number of distinct ways you can arrange the adapters to connect the charging outlet to your device?
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$adapters = $this->data;
		sort( $adapters );

		// Add the charging outlet and the device's built-in adapter
		array_unshift( $adapters, 0 ); // Add the charging outlet (0 jolts)
		$adapters[] = max( $adapters ) + 3; // Add the device's built-in adapter

		// Initialize the number of ways to reach each adapter
		$ways = array_fill( 0, max( $adapters ) + 1, 0 );
		$ways[0] = 1;

		// Calculate the number of ways to reach each adapter
		foreach ( $adapters as $adapter ) {
			for ( $i = 1; $i <= 3; $i++ ) {
				if ( in_array( $adapter - $i, $adapters, true ) ) {
					$ways[ $adapter ] += $ways[ $adapter - $i ];
				}
			}
		}

		return $ways[ max( $adapters ) ];
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

		return array_map( 'intval', $lines );
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
	$day10  = new Day10( $test, $part );
	$result = $day10->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 220,
			'real' => 2263,
		],
		2 => [
			'test' => 19208,
			'real' => 396857386627072,
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
