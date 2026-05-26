<?php

namespace AdventOfCode\Year2017;

/**
 * Day 05: A Maze of Twisty Trampolines, All Alike
 */
class Day05 {
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
	 * Part 1: Count steps to exit the jump maze.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$jumps = $this->data;
		$pos   = 0;
		$steps = 0;
		$len   = count( $jumps );

		while ( $pos >= 0 && $pos < $len ) {
			$offset = $jumps[ $pos ];
			$jumps[ $pos ]++;
			$pos += $offset;
			$steps++;
		}

		return $steps;
	}

	/**
	 * Part 2: Exit the maze with stranger jump rules.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$jumps = $this->data;
		$pos   = 0;
		$steps = 0;
		$len   = count( $jumps );

		while ( $pos >= 0 && $pos < $len ) {
			$offset = $jumps[ $pos ];
			$jumps[ $pos ] += ( $offset >= 3 ) ? -1 : 1;
			$pos += $offset;
			$steps++;
		}

		return $steps;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-05-test.txt' : '/data/day-05.txt';
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
	$day05  = new Day05( $test, $part );
	$result = $day05->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 5,
			'real' => 339351,
		],
		2 => [
			'test' => 10,
			'real' => 24315397,
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
