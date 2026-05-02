<?php

namespace AdventOfCode\Year2016;

/**
 * Day 14: One-Time Pad
 */
class Day14 {
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
	 * The salt string.
	 *
	 * @var string
	 */
	private string $data;

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
	 * Part 1: Find index that produces the 64th key.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->find_key( 0 );
	}

	/**
	 * Part 2: Find 64th key with 2016 rounds of key stretching.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->find_key( 2016 );
	}

	/**
	 * Finds the index that produces the 64th one-time pad key.
	 *
	 * @param int $stretches Number of additional MD5 rounds for key stretching.
	 *
	 * @return integer
	 */
	private function find_key( int $stretches ): int {
		$cache  = [];
		$keys   = 0;

		for ( $i = 0; ; $i++ ) {
			$hash   = $this->get_hash( $i, $stretches, $cache );
			$triple = $this->find_triplet( $hash );

			if ( $triple === null ) {
				continue;
			}

			$quint = str_repeat( $triple, 5 );

			for ( $j = $i + 1; $j <= $i + 1000; $j++ ) {
				if ( str_contains( $this->get_hash( $j, $stretches, $cache ), $quint ) ) {
					$keys++;

					if ( $keys === 64 ) {
						return $i;
					}

					break;
				}
			}
		}
	}

	/**
	 * Gets the hash for an index, using cache.
	 *
	 * @param int   $index     The index.
	 * @param int   $stretches Number of additional MD5 rounds.
	 * @param array &$cache    Hash cache.
	 *
	 * @return string
	 */
	private function get_hash( int $index, int $stretches, array &$cache ): string {
		if ( ! isset( $cache[ $index ] ) ) {
			$hash = md5( $this->data . $index );

			for ( $s = 0; $s < $stretches; $s++ ) {
				$hash = md5( $hash );
			}

			$cache[ $index ] = $hash;
		}

		return $cache[ $index ];
	}

	/**
	 * Finds the first triplet character in a hash.
	 *
	 * @param string $hash The hash string.
	 *
	 * @return string|null The repeated character, or null.
	 */
	private function find_triplet( string $hash ): ?string {
		if ( preg_match( '/(.)\1\1/', $hash, $m ) ) {
			return $m[1];
		}

		return null;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return string The salt.
	 */
	private function parse_data( bool $test ): string {
		$file = $test ? '/data/day-14-test.txt' : '/data/day-14.txt';

		return trim( file_get_contents( __DIR__ . $file ) );
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
	$day14  = new Day14( $test, $part );
	$result = $day14->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 22728,
			'real' => 23890,
		],
		2 => [
			'test' => 22551,
			'real' => 22696,
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
