<?php

namespace AdventOfCode\Year2015;

/**
 * Day 14: Reindeer Olympics
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
	 * Part 1: Find the distance of the winning reindeer after 2503 seconds.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$time = $this->is_test ? 1000 : 2503;
		$max  = 0;

		foreach ( $this->data as $r ) {
			$max = max( $max, $this->distance_at( $r, $time ) );
		}

		return $max;
	}

	/**
	 * Part 2: Award points each second to the leader; find highest score.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$time   = $this->is_test ? 1000 : 2503;
		$points = array_fill( 0, count( $this->data ), 0 );

		for ( $t = 1; $t <= $time; $t++ ) {
			$distances = [];
			foreach ( $this->data as $i => $r ) {
				$distances[ $i ] = $this->distance_at( $r, $t );
			}

			$lead = max( $distances );
			foreach ( $distances as $i => $d ) {
				if ( $d === $lead ) {
					$points[ $i ]++;
				}
			}
		}

		return max( $points );
	}

	/**
	 * Calculates the distance a reindeer has traveled at a given time.
	 *
	 * @param array   $r    Reindeer data.
	 * @param integer $time Time in seconds.
	 *
	 * @return integer
	 */
	private function distance_at( array $r, int $time ): int {
		$cycle       = $r['fly'] + $r['rest'];
		$full_cycles = intdiv( $time, $cycle );
		$remaining   = min( $time % $cycle, $r['fly'] );

		return ( $full_cycles * $r['fly'] + $remaining ) * $r['speed'];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-14-test.txt' : '/data/day-14.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( function ( $line ) {
			preg_match( '/(\w+) can fly (\d+) km\/s for (\d+) seconds, but then must rest for (\d+) seconds/', $line, $m );

			return [
				'name'  => $m[1],
				'speed' => (int) $m[2],
				'fly'   => (int) $m[3],
				'rest'  => (int) $m[4],
			];
		}, $lines );
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
			'test' => 1120,
			'real' => 2655,
		],
		2 => [
			'test' => 689,
			'real' => 1059,
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
