<?php

namespace AdventOfCode\Year2017;

/**
 * Day 24: Electromagnetic Moat
 */
class Day24 {
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

	private int $max_strength = 0;
	private int $max_length   = 0;
	private int $longest_strength = 0;

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
		$this->build( 0, 0, 0, [] );

		return match ( $this->part ) {
			1 => $this->max_strength,
			2 => $this->longest_strength,
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Recursively build bridges, tracking max strength and longest bridge strength.
	 *
	 * @param integer $port     The current open port to match.
	 * @param integer $strength The current bridge strength.
	 * @param integer $length   The current bridge length.
	 * @param array   $used     Indices of used components.
	 */
	private function build( int $port, int $strength, int $length, array $used ): void {
		if ( $strength > $this->max_strength ) {
			$this->max_strength = $strength;
		}

		if ( $length > $this->max_length || ( $length === $this->max_length && $strength > $this->longest_strength ) ) {
			$this->max_length        = $length;
			$this->longest_strength  = $strength;
		}

		foreach ( $this->data as $i => $component ) {
			if ( isset( $used[ $i ] ) ) {
				continue;
			}

			if ( $component[0] === $port ) {
				$used[ $i ] = true;
				$this->build( $component[1], $strength + $component[0] + $component[1], $length + 1, $used );
				unset( $used[ $i ] );
			} elseif ( $component[1] === $port ) {
				$used[ $i ] = true;
				$this->build( $component[0], $strength + $component[0] + $component[1], $length + 1, $used );
				unset( $used[ $i ] );
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
		$file  = $test ? '/data/day-24-test.txt' : '/data/day-24.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( fn( $line ) => array_map( 'intval', explode( '/', $line ) ), $lines );
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
	$day24  = new Day24( $test, $part );
	$result = $day24->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 31,
			'real' => 1656,
		],
		2 => [
			'test' => 19,
			'real' => 1642,
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
