<?php

namespace AdventOfCode\Year2017;

/**
 * Day 16: Permutation Promenade
 */
class Day16 {
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
	 * Parsed dance moves.
	 *
	 * @var array
	 */
	private array $data;

	/**
	 * The line of programs.
	 *
	 * @var array
	 */
	private array $programs;

	public function __construct( bool $test, int $part ) {
		$this->part     = $part;
		$this->is_test  = $test;
		$this->data     = $this->parse_data( $this->is_test );
		$this->programs = $test
			? range( 'a', 'e' )
			: range( 'a', 'p' );
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return string
	 */
	public function run(): string {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Run the dance once and return the order of programs.
	 *
	 * @return string
	 */
	private function solve_part_1(): string {
		$this->dance();

		return implode( '', $this->programs );
	}

	/**
	 * Part 2: Run the dance one billion times using cycle detection.
	 *
	 * @return string
	 */
	private function solve_part_2(): string {
		$initial = $this->programs;
		$seen    = [ implode( '', $this->programs ) ];

		for ( $i = 1; $i <= 1000000000; $i++ ) {
			$this->dance();
			$state = implode( '', $this->programs );

			if ( $state === implode( '', $initial ) ) {
				$remainder      = 1000000000 % $i;
				$this->programs = $initial;

				for ( $j = 0; $j < $remainder; $j++ ) {
					$this->dance();
				}

				return implode( '', $this->programs );
			}

			$seen[] = $state;
		}

		return implode( '', $this->programs );
	}

	/**
	 * Performs one full dance on the programs.
	 */
	private function dance(): void {
		foreach ( $this->data as $move ) {
			switch ( $move[0] ) {
				case 's':
					$count           = (int) $move[1];
					$this->programs  = array_merge(
						array_slice( $this->programs, -$count ),
						array_slice( $this->programs, 0, -$count )
					);
					break;
				case 'x':
					$a = $move[1];
					$b = $move[2];
					[ $this->programs[ $a ], $this->programs[ $b ] ] = [ $this->programs[ $b ], $this->programs[ $a ] ];
					break;
				case 'p':
					$a   = array_search( $move[1], $this->programs );
					$b   = array_search( $move[2], $this->programs );
					[ $this->programs[ $a ], $this->programs[ $b ] ] = [ $this->programs[ $b ], $this->programs[ $a ] ];
					break;
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
		$file  = $test ? '/data/day-16-test.txt' : '/data/day-16.txt';
		$input = trim( file_get_contents( __DIR__ . $file ) );
		$moves = explode( ',', $input );

		$parsed = [];
		foreach ( $moves as $move ) {
			$type = $move[0];
			$rest = substr( $move, 1 );

			switch ( $type ) {
				case 's':
					$parsed[] = [ 's', (int) $rest ];
					break;
				case 'x':
					$parts    = explode( '/', $rest );
					$parsed[] = [ 'x', (int) $parts[0], (int) $parts[1] ];
					break;
				case 'p':
					$parts    = explode( '/', $rest );
					$parsed[] = [ 'p', $parts[0], $parts[1] ];
					break;
			}
		}

		return $parsed;
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
	$day16  = new Day16( $test, $part );
	$result = $day16->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 'baedc',
			'real' => 'ceijbfoamgkdnlph',
		],
		2 => [
			'test' => 'abcde',
			'real' => 'pnhajoekigcbflmd',
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
