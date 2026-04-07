<?php

namespace AdventOfCode\Year2015;

/**
 * Day 20: Infinite Elves and Infinite Houses
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
	 * Part 1: Find lowest house number getting at least target presents.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$target = $this->data[0];
		$limit  = $target / 10;
		$houses = array_fill( 0, $limit + 1, 0 );

		for ( $elf = 1; $elf <= $limit; $elf++ ) {
			for ( $h = $elf; $h <= $limit; $h += $elf ) {
				$houses[ $h ] += $elf * 10;
			}
		}

		for ( $i = 1; $i <= $limit; $i++ ) {
			if ( $houses[ $i ] >= $target ) {
				return $i;
			}
		}

		return 0;
	}

	/**
	 * Part 2: Each elf visits only 50 houses, delivering 11x presents.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$target = $this->data[0];
		$limit  = intdiv( $target, 11 );
		$houses = array_fill( 0, $limit + 1, 0 );

		for ( $elf = 1; $elf <= $limit; $elf++ ) {
			$max_house = min( $elf * 50, $limit );
			for ( $h = $elf; $h <= $max_house; $h += $elf ) {
				$houses[ $h ] += $elf * 11;
			}
		}

		for ( $i = 1; $i <= $limit; $i++ ) {
			if ( $houses[ $i ] >= $target ) {
				return $i;
			}
		}

		return 0;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-20-test.txt' : '/data/day-20.txt';

		return [ (int) trim( file_get_contents( __DIR__ . $file ) ) ];
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
			'test' => 8,
			'real' => 831600,
		],
		2 => [
			'test' => 8,
			'real' => 884520,
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
