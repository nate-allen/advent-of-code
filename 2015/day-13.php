<?php

namespace AdventOfCode\Year2015;

/**
 * Day 13: Knights of the Dinner Table
 */
class Day13 {
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
	 * Happiness map.
	 *
	 * @var array
	 */
	private array $data;

	/**
	 * List of people.
	 *
	 * @var array
	 */
	private array $people;

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
	 * Part 1: Find optimal seating arrangement.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->best_happiness( $this->people );
	}

	/**
	 * Part 2: Add yourself with 0 happiness, find optimal arrangement.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$people = $this->people;

		foreach ( $people as $person ) {
			$this->data['Me'][ $person ] = 0;
			$this->data[ $person ]['Me'] = 0;
		}

		$people[] = 'Me';

		return $this->best_happiness( $people );
	}

	/**
	 * Finds the maximum happiness for any circular seating arrangement.
	 *
	 * @param array $people List of people to seat.
	 *
	 * @return integer
	 */
	private function best_happiness( array $people ): int {
		$first = array_shift( $people );
		$max   = PHP_INT_MIN;

		foreach ( $this->permutations( $people ) as $perm ) {
			$arrangement = array_merge( [ $first ], $perm );
			$happiness   = 0;
			$count       = count( $arrangement );

			for ( $i = 0; $i < $count; $i++ ) {
				$left  = $arrangement[ ( $i - 1 + $count ) % $count ];
				$right = $arrangement[ ( $i + 1 ) % $count ];
				$happiness += $this->data[ $arrangement[ $i ] ][ $left ];
				$happiness += $this->data[ $arrangement[ $i ] ][ $right ];
			}

			$max = max( $max, $happiness );
		}

		return $max;
	}

	/**
	 * Generates all permutations of the given array.
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

		for ( $i = 0; $i < count( $items ); $i++ ) {
			$rest = array_merge( array_slice( $items, 0, $i ), array_slice( $items, $i + 1 ) );
			foreach ( $this->permutations( $rest ) as $perm ) {
				yield array_merge( [ $items[ $i ] ], $perm );
			}
		}
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file   = $test ? '/data/day-13-test.txt' : '/data/day-13.txt';
		$lines  = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$happy  = [];
		$people = [];

		foreach ( $lines as $line ) {
			preg_match( '/(\w+) would (gain|lose) (\d+) happiness units by sitting next to (\w+)/', $line, $m );
			$val = (int) $m[3] * ( $m[2] === 'lose' ? -1 : 1 );
			$happy[ $m[1] ][ $m[4] ] = $val;
			$people[ $m[1] ] = true;
		}

		$this->people = array_keys( $people );

		return $happy;
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
	$day13  = new Day13( $test, $part );
	$result = $day13->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 330,
			'real' => 618,
		],
		2 => [
			'test' => 286,
			'real' => 601,
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
