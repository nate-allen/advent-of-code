<?php

namespace AdventOfCode\Year2015;

/**
 * Day 19: Medicine for Rudolph
 */
class Day19 {
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
	 * Replacement rules.
	 *
	 * @var array
	 */
	private array $rules;

	/**
	 * The medicine molecule.
	 *
	 * @var string
	 */
	private string $molecule;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->parse_data( $this->is_test );
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
	 * Part 1: Count distinct molecules after one replacement.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$molecules = [];

		foreach ( $this->rules as [ $from, $to ] ) {
			$pos = 0;
			while ( ( $pos = strpos( $this->molecule, $from, $pos ) ) !== false ) {
				$new = substr( $this->molecule, 0, $pos ) . $to . substr( $this->molecule, $pos + strlen( $from ) );
				$molecules[ $new ] = true;
				$pos++;
			}
		}

		return count( $molecules );
	}

	/**
	 * Part 2: Find fewest steps to go from e to the medicine molecule.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		// Count tokens (uppercase letters = start of each element).
		$tokens = preg_match_all( '/[A-Z]/', $this->molecule );
		$rn     = substr_count( $this->molecule, 'Rn' );
		$ar     = substr_count( $this->molecule, 'Ar' );
		$y      = substr_count( $this->molecule, 'Y' );

		return $tokens - $rn - $ar - 2 * $y - 1;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): void {
		$file    = $test ? '/data/day-19-test.txt' : '/data/day-19.txt';
		$parts   = explode( "\n\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$rules   = [];

		foreach ( explode( "\n", $parts[0] ) as $line ) {
			[ $from, $to ] = explode( ' => ', $line );
			$rules[] = [ $from, $to ];
		}

		$this->rules    = $rules;
		$this->molecule = $parts[1];
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
	$day19  = new Day19( $test, $part );
	$result = $day19->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 4,
			'real' => 518,
		],
		2 => [
			'test' => 3,
			'real' => 200,
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
