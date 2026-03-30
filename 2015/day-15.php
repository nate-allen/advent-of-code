<?php

namespace AdventOfCode\Year2015;

/**
 * Day 15: Science for Hungry People
 */
class Day15 {
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
	 * Part 1: Find the highest-scoring cookie.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->find_best( false );
	}

	/**
	 * Part 2: Find the highest-scoring cookie with exactly 500 calories.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->find_best( true );
	}

	/**
	 * Finds the best cookie score by brute-forcing all combinations.
	 *
	 * @param bool $calorie_limit Whether to enforce 500 calorie limit.
	 *
	 * @return integer
	 */
	private function find_best( bool $calorie_limit ): int {
		$best = 0;

		foreach ( $this->combinations( count( $this->data ), 100 ) as $amounts ) {
			$props = [ 0, 0, 0, 0, 0 ];

			foreach ( $this->data as $i => $ingredient ) {
				for ( $p = 0; $p < 5; $p++ ) {
					$props[ $p ] += $amounts[ $i ] * $ingredient[ $p ];
				}
			}

			if ( $calorie_limit && $props[4] !== 500 ) {
				continue;
			}

			$score = 1;
			for ( $p = 0; $p < 4; $p++ ) {
				$score *= max( 0, $props[ $p ] );
			}

			$best = max( $best, $score );
		}

		return $best;
	}

	/**
	 * Generates all ways to split total among n buckets.
	 *
	 * @param integer $n     Number of buckets.
	 * @param integer $total Sum target.
	 *
	 * @return \Generator
	 */
	private function combinations( int $n, int $total ): \Generator {
		if ( $n === 1 ) {
			yield [ $total ];
			return;
		}

		for ( $i = 0; $i <= $total; $i++ ) {
			foreach ( $this->combinations( $n - 1, $total - $i ) as $rest ) {
				yield array_merge( [ $i ], $rest );
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
		$file  = $test ? '/data/day-15-test.txt' : '/data/day-15.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( function ( $line ) {
			preg_match( '/\w+: capacity (-?\d+), durability (-?\d+), flavor (-?\d+), texture (-?\d+), calories (-?\d+)/', $line, $m );

			return array_map( 'intval', [ $m[1], $m[2], $m[3], $m[4], $m[5] ] );
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
	$day15  = new Day15( $test, $part );
	$result = $day15->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 62842880,
			'real' => 222870,
		],
		2 => [
			'test' => 57600000,
			'real' => 117936,
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
