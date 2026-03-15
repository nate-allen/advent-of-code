<?php

namespace AdventOfCode\Year2015;

/**
 * Day 07: Some Assembly Required
 */
class Day07 {
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
	 * Part 1: Find the signal on wire a.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$cache = [];

		return $this->evaluate( 'a', $cache );
	}

	/**
	 * Part 2: Override wire b with part 1's result, find new signal on wire a.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$cache          = [];
		$signal_a       = $this->evaluate( 'a', $cache );
		$cache          = [ 'b' => $signal_a ];

		return $this->evaluate( 'a', $cache );
	}

	/**
	 * Recursively evaluates the signal for a given wire.
	 *
	 * @param string $wire  The wire to evaluate.
	 * @param array  $cache Memoization cache.
	 *
	 * @return integer
	 */
	private function evaluate( string $wire, array &$cache ): int {
		if ( isset( $cache[ $wire ] ) ) {
			return $cache[ $wire ];
		}

		if ( is_numeric( $wire ) ) {
			return (int) $wire;
		}

		$parts  = $this->data[ $wire ];
		$result = match ( count( $parts ) ) {
			1 => $this->evaluate( $parts[0], $cache ),
			2 => ~$this->evaluate( $parts[1], $cache ) & 0xFFFF,
			3 => match ( $parts[1] ) {
				'AND'    => $this->evaluate( $parts[0], $cache ) & $this->evaluate( $parts[2], $cache ),
				'OR'     => $this->evaluate( $parts[0], $cache ) | $this->evaluate( $parts[2], $cache ),
				'LSHIFT' => ( $this->evaluate( $parts[0], $cache ) << (int) $parts[2] ) & 0xFFFF,
				'RSHIFT' => $this->evaluate( $parts[0], $cache ) >> (int) $parts[2],
			},
		};

		$cache[ $wire ] = $result;

		return $result;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-07-test.txt' : '/data/day-07.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$wires = [];

		foreach ( $lines as $line ) {
			[ $expr, $target ] = explode( ' -> ', $line );
			$wires[ $target ] = explode( ' ', $expr );
		}

		return $wires;
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
	$day07  = new Day07( $test, $part );
	$result = $day07->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 72,
			'real' => 956,
		],
		2 => [
			'test' => 72,
			'real' => 40149,
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
