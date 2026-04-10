<?php

namespace AdventOfCode\Year2015;

/**
 * Day 23: Opening the Turing Lock
 */
class Day23 {
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
	 * Part 1: Run program, return value of register b.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->execute( [ 'a' => 0, 'b' => 0 ] );
	}

	/**
	 * Part 2: Run program with register a starting at 1.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->execute( [ 'a' => 1, 'b' => 0 ] );
	}

	/**
	 * Executes the program with the given initial register values.
	 *
	 * @param array $reg Initial register values.
	 *
	 * @return integer Value of register b (or a for test).
	 */
	private function execute( array $reg ): int {
		$ip  = 0;
		$len = count( $this->data );

		while ( $ip >= 0 && $ip < $len ) {
			$inst = $this->data[ $ip ];

			switch ( $inst[0] ) {
				case 'hlf':
					$reg[ $inst[1] ] = intdiv( $reg[ $inst[1] ], 2 );
					$ip++;
					break;
				case 'tpl':
					$reg[ $inst[1] ] *= 3;
					$ip++;
					break;
				case 'inc':
					$reg[ $inst[1] ]++;
					$ip++;
					break;
				case 'jmp':
					$ip += (int) $inst[1];
					break;
				case 'jie':
					$ip += ( $reg[ $inst[1] ] % 2 === 0 ) ? (int) $inst[2] : 1;
					break;
				case 'jio':
					$ip += ( $reg[ $inst[1] ] === 1 ) ? (int) $inst[2] : 1;
					break;
			}
		}

		return $this->is_test ? $reg['a'] : $reg['b'];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-23-test.txt' : '/data/day-23.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( function ( $line ) {
			$line  = str_replace( ',', '', $line );
			$parts = explode( ' ', $line );

			return $parts;
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
	$day23  = new Day23( $test, $part );
	$result = $day23->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 2,
			'real' => 170,
		],
		2 => [
			'test' => 2,
			'real' => 247,
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
