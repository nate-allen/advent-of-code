<?php

namespace AdventOfCode\Year2017;

/**
 * Day 15: Dueling Generators
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
	 * Part 1: Count matching lowest 16 bits after 40 million pairs.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$a     = $this->data[0];
		$b     = $this->data[1];
		$count = 0;

		for ( $i = 0; $i < 40000000; $i++ ) {
			$a = ( $a * 16807 ) % 2147483647;
			$b = ( $b * 48271 ) % 2147483647;

			if ( ( $a & 0xFFFF ) === ( $b & 0xFFFF ) ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Part 2: Count matches with picky generators over 5 million pairs.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$a     = $this->data[0];
		$b     = $this->data[1];
		$count = 0;

		for ( $i = 0; $i < 5000000; $i++ ) {
			do {
				$a = ( $a * 16807 ) % 2147483647;
			} while ( $a % 4 !== 0 );

			do {
				$b = ( $b * 48271 ) % 2147483647;
			} while ( $b % 8 !== 0 );

			if ( ( $a & 0xFFFF ) === ( $b & 0xFFFF ) ) {
				$count++;
			}
		}

		return $count;
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

		$starts = [];
		foreach ( $lines as $line ) {
			preg_match( '/(\d+)$/', $line, $matches );
			$starts[] = (int) $matches[1];
		}

		return $starts;
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
			'test' => 588,
			'real' => 612,
		],
		2 => [
			'test' => 309,
			'real' => 285,
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
