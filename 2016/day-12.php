<?php

namespace AdventOfCode\Year2016;

/**
 * Day 12: Leonardo's Monorail
 */
class Day12 {
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
	 * Part 1: Execute assembunny code, return value in register a.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->execute( [ 'a' => 0, 'b' => 0, 'c' => 0, 'd' => 0 ] );
	}

	/**
	 * Part 2: Execute with register c initialized to 1.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->execute( [ 'a' => 0, 'b' => 0, 'c' => 1, 'd' => 0 ] );
	}

	/**
	 * Executes the assembunny instructions with the given initial registers.
	 *
	 * @param array $regs Initial register values.
	 *
	 * @return integer Value in register a after execution.
	 */
	private function execute( array $regs ): int {
		$instructions = $this->data;
		$ip           = 0;
		$len          = count( $instructions );

		while ( $ip < $len ) {
			$inst = $instructions[ $ip ];

			switch ( $inst[0] ) {
				case 'cpy':
					$val          = is_numeric( $inst[1] ) ? (int) $inst[1] : $regs[ $inst[1] ];
					$regs[ $inst[2] ] = $val;
					break;
				case 'inc':
					$regs[ $inst[1] ]++;
					break;
				case 'dec':
					$regs[ $inst[1] ]--;
					break;
				case 'jnz':
					$val = is_numeric( $inst[1] ) ? (int) $inst[1] : $regs[ $inst[1] ];
					if ( $val !== 0 ) {
						$ip += (int) $inst[2];
						continue 2;
					}
					break;
			}

			$ip++;
		}

		return $regs['a'];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of instruction arrays [opcode, arg1, arg2?].
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-12-test.txt' : '/data/day-12.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( fn( $line ) => explode( ' ', $line ), $lines );
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
	$day12  = new Day12( $test, $part );
	$result = $day12->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 42,
			'real' => 318020,
		],
		2 => [
			'test' => 42,
			'real' => 9227674,
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
